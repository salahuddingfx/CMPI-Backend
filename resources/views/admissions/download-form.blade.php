<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: sans-serif; font-size: 12px; margin: 20px; }
  .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
  .header h1 { font-size: 18px; margin: 0; }
  .header h2 { font-size: 14px; margin: 5px 0 0 0; font-weight: normal; }
  .header p { font-size: 10px; margin: 3px 0 0 0; color: #444; }
  .title { text-align: center; font-size: 16px; font-weight: bold; margin: 15px 0; background: #f0f0f0; padding: 8px; }
  table { width: 100%; border-collapse: collapse; margin: 10px 0; }
  td { padding: 6px 8px; border: 1px solid #ccc; vertical-align: top; }
  td.label { width: 35%; font-weight: bold; background: #f8f8f8; }
  .section { font-size: 13px; font-weight: bold; background: #e8e8e8; padding: 6px 8px; margin: 15px 0 5px 0; border: 1px solid #ccc; }
  .checkbox-group { margin: 5px 0; }
  .checkbox-group label { margin-right: 20px; }
  .checkbox { display: inline-block; width: 14px; height: 14px; border: 1px solid #000; margin-right: 5px; vertical-align: middle; }
  .signature { margin-top: 40px; display: flex; justify-content: space-between; }
  .signature-box { width: 45%; text-align: center; }
  .signature-line { border-top: 1px solid #000; margin-top: 50px; padding-top: 5px; }
  .footer { text-align: center; font-size: 9px; margin-top: 20px; color: #666; border-top: 1px solid #ccc; padding-top: 8px; }
  .note { font-size: 10px; background: #fffde7; border: 1px solid #e6d500; padding: 8px; margin: 10px 0; }
  .note strong { color: #b8860b; }
</style>
</head>
<body>

<div class="header">
  <h1>COX'S BAZAR MODEL POLYTECHNIC INSTITUTE (CMPI)</h1>
  <h2>Application Form for Admission</h2>
  <p>Address: Cox's Bazar, Bangladesh | Phone: 01XXXXXXXXX | Web: www.cmpi.edu.bd</p>
</div>

<div class="title">ADMISSION APPLICATION FORM {{ date('Y') }}-{{ date('Y')+1 }}</div>

<div class="section">1. Personal Information</div>
<table>
  <tr>
    <td class="label">Full Name</td>
    <td style="border-bottom: 1px dotted #000; width: 65%;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Father's Name</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Mother's Name</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Date of Birth</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Blood Group</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Phone Number</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Email Address</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Permanent Address</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
</table>

<div class="section">2. Academic Information</div>
<table>
  <tr>
    <td class="label">SSC / Equivalent GPA</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">HSC / Equivalent GPA</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Board / Institution</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
  <tr>
    <td class="label">Passing Year (SSC)</td>
    <td style="border-bottom: 1px dotted #000;">&nbsp;</td>
  </tr>
</table>

<div class="section">3. Department Preference</div>
<div class="checkbox-group" style="padding: 8px;">
  <label><span class="checkbox"></span> Computer Science & Technology (CST)</label><br>
  <label><span class="checkbox"></span> Civil Technology</label><br>
  <label><span class="checkbox"></span> Electrical Technology</label><br>
  <label><span class="checkbox"></span> Electronics Technology</label><br>
  <label><span class="checkbox"></span> Telecommunications Technology</label><br>
  <label><span class="checkbox"></span> Mechanical Technology</label><br>
  <label><span class="checkbox"></span> Marine Technology</label>
</div>

<div class="section">4. Required Documents Checklist</div>
<div class="note">
  <strong>Important:</strong> All original documents must be submitted at the time of admission. 
  Photocopies will be verified against originals.
</div>
<table>
  <tr>
    <td class="label">Documents</td>
    <td style="text-align: center; width: 15%;">Attached?</td>
  </tr>
  <tr>
    <td>1. SSC / Equivalent Certificate & Marksheat</td>
    <td style="text-align: center;"><span class="checkbox"></span></td>
  </tr>
  <tr>
    <td>2. National ID Card (NID) / Birth Certificate</td>
    <td style="text-align: center;"><span class="checkbox"></span></td>
  </tr>
  <tr>
    <td>3. 4 Passport-size Photographs</td>
    <td style="text-align: center;"><span class="checkbox"></span></td>
  </tr>
  <tr>
    <td>4. Guardian's NID Copy</td>
    <td style="text-align: center;"><span class="checkbox"></span></td>
  </tr>
  <tr>
    <td>5. Transfer Certificate</td>
    <td style="text-align: center;"><span class="checkbox"></span></td>
  </tr>
  <tr>
    <td>6. Proshongso Potro (Character Certificate from previous school)</td>
    <td style="text-align: center;"><span class="checkbox"></span></td>
  </tr>
</table>

<div class="section">5. Declaration</div>
<p style="font-size: 11px; padding: 8px;">
  I hereby declare that all the information provided above is true and correct to the best of my knowledge. 
  I understand that if any information is found to be false or misleading, my application may be cancelled 
  at any stage, and I may be disqualified from admission.
</p>

<div class="signature">
  <div class="signature-box">
    <div class="signature-line">Applicant's Signature</div>
  </div>
  <div class="signature-box">
    <div class="signature-line">Guardian's Signature</div>
  </div>
</div>

<div class="footer">
  <p>Cox's Bazar Model Polytechnic Institute (CMPI) | Admission Session {{ date('Y') }}-{{ date('Y')+1 }}</p>
  <p>For queries, contact: info@cmpi.edu.bd | This form must be submitted along with all required documents.</p>
</div>

</body>
</html>
