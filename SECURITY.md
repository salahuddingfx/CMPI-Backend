# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 12.x    | :white_check_mark: |
| < 12.0  | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within CMPI, please send an email to [salahuddingfx](https://github.com/salahuddingfx). All security vulnerabilities will be promptly addressed.

**Please do not report security vulnerabilities through public GitHub issues.**

### What to include

When reporting a vulnerability, please include:

- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact
- Suggested fix (if any)

### Response Timeline

- **Acknowledgment**: Within 48 hours of your report
- **Initial Assessment**: Within 1 week
- **Fix Deployment**: Depending on severity, within 1-4 weeks

## Security Best Practices

When using or contributing to this project:

- Never commit sensitive credentials (API keys, passwords, tokens) to the repository
- Use environment variables for all configuration secrets
- Keep dependencies up to date
- Follow Laravel security best practices
- Validate and sanitize all user input
- Use HTTPS in production
- Implement proper CORS configuration

## Authentication

This project uses **Laravel Sanctum** for API token authentication. Please ensure:

- Tokens are stored securely on the client side
- Tokens are transmitted over HTTPS only
- Expired tokens are properly handled
- Rate limiting is enabled for auth endpoints

## Dependency Security

We regularly audit our dependencies for known vulnerabilities. If you discover a vulnerability in a dependency:

1. Report it to us immediately
2. Do not open a public issue
3. We will work to update or replace the affected dependency
