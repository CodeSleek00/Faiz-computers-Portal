# Code Sleek Foundation — Certificate Verification System

## What it does

1. Admin logs in.
2. Admin enters student name, enrollment number, course, issue date and photo.
3. The system creates a unique Certificate ID such as `CSF-2026-A1B2C3D4`.
4. A QR code is generated for the certificate.
5. Scanning the QR code opens `verify.php`.
6. The verification page shows **COURSE COMPLETED** and the stored certificate details.
7. The certificate can be printed/saved as PDF from the browser.

## Hostinger installation

1. Create a MySQL database in Hostinger.
2. Open phpMyAdmin and import `db.sql`.
3. Upload all project files to `public_html/certificate-system/` (or your desired folder).
4. Edit `config.php`:
   - database name
   - database user
   - database password
   - `$siteUrl` (for example `https://example.com/certificate-system`)
   - change `$adminPassword`
5. Make sure the `uploads` folder is writable. The app creates it automatically when the first photo is uploaded.
6. Open `https://yourdomain.com/certificate-system/admin.php`.
7. Log in and generate the first certificate.

## QR generation

The QR image uses QuickChart's public QR endpoint. The QR contains your own verification URL. The actual certificate verification is handled by your PHP/MySQL site.

For a completely self-hosted QR generator later, replace the QuickChart image URL in `certificate.php` with a PHP QR library.

## Important

The supplied screenshot is used as the certificate background. The existing sample values in the screenshot are masked and replaced with live student data. If you provide the original editable certificate template later (PSD/AI/Canva/PPTX/Word/PDF), the positioning can be made even more exact.
