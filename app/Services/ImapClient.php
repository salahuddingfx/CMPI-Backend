<?php

namespace App\Services;

class ImapClient
{
    private $socket = null;
    private $tagCount = 1;
    private $host;
    private $port;

    public function __construct($host = 'mail.cmpi.edu.bd', $port = 993)
    {
        $this->host = $host;
        $this->port = $port;
    }

    private function getTag()
    {
        return 'A' . ($this->tagCount++);
    }

    private function sendCommand($command)
    {
        $tag = $this->getTag();
        $fullCommand = "{$tag} {$command}\r\n";
        fwrite($this->socket, $fullCommand);
        return $tag;
    }

    private function readResponse($tag)
    {
        $lines = [];
        while ($line = fgets($this->socket)) {
            $lines[] = $line;
            if (str_starts_with($line, "{$tag} ")) {
                break;
            }
        }
        return $lines;
    }

    public function fetchInbox($username, $password, $limit = 20)
    {
        try {
            // 1. Connect over SSL/TLS
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
            
            $connectionStr = "ssl://{$this->host}:{$this->port}";
            $this->socket = @stream_socket_client($connectionStr, $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
            
            if (!$this->socket) {
                return ['status' => 'error', 'message' => "Connection failed: {$errstr}"];
            }

            stream_set_timeout($this->socket, 5);

            // Read greeting
            $greeting = fgets($this->socket);
            if (!str_starts_with($greeting, '* OK')) {
                $this->close();
                return ['status' => 'error', 'message' => 'Invalid greeting from mail server.'];
            }

            // 2. Login
            $tag = $this->sendCommand("LOGIN \"{$username}\" \"{$password}\"");
            $response = $this->readResponse($tag);
            $lastLine = end($response);
            
            if (strpos($lastLine, "{$tag} OK") === false) {
                $this->close();
                return ['status' => 'error', 'message' => 'Invalid email or password on mail server.'];
            }

            // 3. Select Inbox
            $tag = $this->sendCommand("SELECT INBOX");
            $response = $this->readResponse($tag);
            $lastLine = end($response);
            
            if (strpos($lastLine, "{$tag} OK") === false) {
                $this->close();
                return ['status' => 'error', 'message' => 'Failed to select INBOX.'];
            }

            // Find how many emails exist
            $exists = 0;
            foreach ($response as $line) {
                if (preg_match('/\* (\d+) EXISTS/i', $line, $m)) {
                    $exists = (int)$m[1];
                }
            }

            if ($exists === 0) {
                $this->close();
                return ['status' => 'success', 'emails' => []];
            }

            // 4. Fetch last X messages headers (using ENVELOPE)
            $start = max(1, $exists - $limit + 1);
            $end = $exists;
            
            $tag = $this->sendCommand("FETCH {$start}:{$end} (ENVELOPE)");
            $response = $this->readResponse($tag);

            $emails = [];
            $currentMsg = null;
            
            foreach ($response as $line) {
                if (preg_match('/\* (\d+) FETCH \((.*)/i', $line, $m)) {
                    $msgId = $m[1];
                    $envelopeStr = $m[2];
                    
                    // Basic parsing of ENVELOPE (date, subject, from)
                    // Format: ENVELOPE ("date" "subject" (( "name" nil "mailbox" "host" )) ...)
                    $date = 'Unknown';
                    if (preg_match('/"([^"]+)"/i', $envelopeStr, $dateMatch)) {
                        $date = $dateMatch[1];
                    }

                    $subject = 'No Subject';
                    if (preg_match('/"[^"]+"\s+"([^"]+)"/i', $envelopeStr, $subMatch)) {
                        $subject = $subMatch[1];
                    }

                    $from = 'Unknown Sender';
                    if (preg_match('/\(\(([^)]+)\)\)/i', $envelopeStr, $fromMatch)) {
                        $fromParts = explode(' ', $fromMatch[1]);
                        $mailbox = trim($fromParts[2] ?? '', '"');
                        $host = trim($fromParts[3] ?? '', '"');
                        if ($mailbox && $host) {
                            $from = "{$mailbox}@{$host}";
                        }
                    }

                    $emails[] = [
                        'id' => "live_{$msgId}",
                        'from_email' => $from,
                        'to_email' => $username,
                        'subject' => $subject,
                        'preview' => "Live email from mail server.",
                        'body' => null, // Fetch on demand
                        'date' => date('Y-m-d', strtotime($date)),
                        'folder' => 'inbox',
                        'read' => false,
                        'starred' => false,
                        'label' => null,
                        'is_live' => true
                    ];
                }
            }

            $this->close();
            // Sort by message ID descending (newest first)
            return ['status' => 'success', 'emails' => array_reverse($emails)];

        } catch (\Exception $e) {
            $this->close();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function fetchMessageBody($username, $password, $msgId)
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
            $this->socket = @stream_socket_client("ssl://{$this->host}:{$this->port}", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $context);
            if (!$this->socket) return null;

            fgets($this->socket);
            
            $tag = $this->sendCommand("LOGIN \"{$username}\" \"{$password}\"");
            $this->readResponse($tag);

            $tag = $this->sendCommand("SELECT INBOX");
            $this->readResponse($tag);

            // Fetch the text body
            $tag = $this->sendCommand("FETCH {$msgId} (BODY.PEEK[TEXT])");
            $response = $this->readResponse($tag);
            $this->close();

            // Extract body text between tags
            $body = '';
            $capture = false;
            foreach ($response as $line) {
                if ($capture) {
                    if (str_starts_with($line, "{$tag} ")) {
                        break;
                    }
                    $body .= $line;
                }
                if (preg_match('/\* \d+ FETCH/i', $line)) {
                    $capture = true;
                }
            }

            // Basic clean up of body
            $body = trim($body);
            if (str_ends_with($body, ')')) {
                $body = substr($body, 0, -1);
            }

            return trim($body);

        } catch (\Exception $e) {
            $this->close();
            return null;
        }
    }

    private function close()
    {
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
    }
}
