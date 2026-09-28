FAIZ COMPUTER INSTITUTE - STUDY MATERIAL ADMIN

INSTALLATION
1. Upload this "study_admin" folder inside your existing admin directory.
2. The module expects your existing database connection at:
   ../database_connection/db_connect.php
   If your connection file is elsewhere, edit includes/config.php.
3. Run the previously provided SQL tables:
   study_courses
   study_topics
   study_contents
   study_content_targets
   study_progress
   study_content_attachments
4. Make sure PHP can write to:
   uploads/videos
   uploads/notes
   uploads/pdf
   uploads/documents
   uploads/practical
   uploads/attachments
   uploads/thumbnails
5. Open study_admin/index.php from the admin area.

IMPORTANT
- No demo/sample data is inserted by this package.
- Students are read ONLY from students26.
- Batch membership is read ONLY from student_batches where student_table='students26'.
- Batch assignment uses the batch_id values already present in student_batches.
- No students or batches are created by this module.
- Existing admin authentication can be enforced from includes/auth.php.

AUTHENTICATION
By default auth.php checks common admin session keys:
admin_id, admin_logged_in, admin, is_admin_logged_in.
If your existing admin session uses another key, edit includes/auth.php.

UPLOADS
Video upload limit is controlled by PHP/server upload_max_filesize and post_max_size.
The code also applies an application-level 500 MB limit to videos and 25 MB to normal files.
For very large videos, increase PHP limits in hosting and consider external/object storage later.

SECURITY
- Prepared statements are used for database operations.
- Upload filenames are randomized.
- Dangerous executable extensions are blocked.
- Content output is escaped by default.
- Keep the study_admin folder behind your admin authentication.
