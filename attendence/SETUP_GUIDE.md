# 📚 Modern Attendance System - Setup Guide

## ✨ What's New

Your attendance system has been completely rebuilt with:

- ✅ **SQLite Database** - Modern, file-based database (no MySQL required)
- ✅ **Beautiful Modern UI** - Responsive design with gradient headers and smooth animations
- ✅ **Better Organization** - Clean code structure and proper error handling
- ✅ **Student Dashboard** - Calendar view + detailed records
- ✅ **Admin Dashboard** - Bulk marking, filtering, and statistics
- ✅ **Real-time Summary** - Live count of Present/Absent/Leave

## 🗂️ Files Structure

```
attendence/
├── sqlite_config.php          # SQLite database connection & initialization
├── init_sqlite.php            # Database schema initialization (optional)
├── mark_attendance.php        # Admin: Mark student attendance
├── save_attendance.php        # Process attendance submission
├── view_attendance.php        # Admin: View attendance records
├── student_attendance.php     # Student: View own attendance
├── manage_student_status.php  # (Keep existing if using)
├── SETUP_GUIDE.md            # This file
└── database/                  # Created automatically
    └── attendance.db         # SQLite database file
```

## 🚀 Quick Start

### 1. Initialize Database
The database is **automatically created** on first access. Simply visit any attendance page and the SQLite database will be initialized with the required tables.

Optional: Visit `init_sqlite.php` to manually initialize:
```
http://yoursite.com/attendence/init_sqlite.php
```

### 2. Access the System

**For Admins/Staff:**
- Mark Attendance: `mark_attendance.php`
- View Records: `view_attendance.php`

**For Students:**
- My Attendance: `student_attendance.php`

## 📊 Key Features

### Admin Functions

#### Mark Attendance
- Select date to mark attendance
- Bulk actions (Mark All Present, All Absent, All Leave)
- Individual remarks for each student
- Real-time summary with Present/Absent/Leave counts
- Responsive table design

#### View Records
- Filter by month
- Statistics dashboard showing:
  - Total records marked
  - Present count
  - Absent count
  - Leave count
  - Distinct students

### Student Functions

#### My Attendance
- **Calendar View** - Visual representation of attendance
  - Green = Present
  - Red = Absent
  - Yellow = Leave
  - White = No record
  
- **Statistics** - Current month and overall summaries
  - Total days, Present days, Absent days, Leave days
  - Attendance percentage with progress bar

- **Detailed Records** - Table view of all attendance entries with:
  - Date and status
  - Remarks/notes
  - When it was marked

## 🗄️ Database Schema

### attendance table
```sql
CREATE TABLE attendance (
    id INTEGER PRIMARY KEY,
    student_id INTEGER,
    student_name TEXT,
    enrollment_id TEXT,
    course TEXT,
    batch TEXT,
    attendance_date DATE,
    status TEXT (Present/Absent/Leave),
    remarks TEXT,
    marked_by INTEGER,
    marked_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

### attendance_summary table
```sql
CREATE TABLE attendance_summary (
    id INTEGER PRIMARY KEY,
    student_id INTEGER UNIQUE,
    total_days INTEGER,
    present_days INTEGER,
    absent_days INTEGER,
    leave_days INTEGER,
    attendance_percentage REAL,
    last_updated TIMESTAMP
)
```

## 🔐 Security & Access Control

- Attendance marking requires `admin` or `staff` role (check `session['role']`)
- Student attendance viewing requires login (check `session['student_id']`)
- Session validation on all pages
- SQL injection protection using prepared statements
- Data escaping with `htmlspecialchars()`

## 💾 Database File Location

The SQLite database is stored at:
```
/database/attendance.db
```

Make sure the `/database` directory is writable:
```bash
chmod 755 /database
```

## 🎨 Design Features

### Modern UI Elements
- Gradient backgrounds
- Smooth hover effects
- Color-coded status badges
- Responsive grid layouts
- Mobile-friendly design

### Color Scheme
- **Primary**: #667eea (Purple)
- **Present**: #10b981 (Green)
- **Absent**: #ef4444 (Red)
- **Leave**: #f59e0b (Orange)

## 🔄 Customization

### Modify Student Data Source
In `mark_attendance.php`, update the students query to match your database structure:

```php
// Current example:
$query = "SELECT * FROM (
    SELECT student_id AS id, name, enrollment_id, course FROM students
    UNION ALL
    SELECT id, name, enrollment_id, course FROM students26
) AS all_students";

// Adjust table names and columns as needed
```

### Change Status Options
Edit the select options in `mark_attendance.php`:
```html
<option value="Present">Present</option>
<option value="Absent">Absent</option>
<option value="Leave">Leave</option>
```

## 📱 Mobile Support

All pages are fully responsive:
- Adapts to tablets (768px breakpoint)
- Touch-friendly interface
- Horizontal scroll for large tables
- Optimized font sizes

## 🐛 Troubleshooting

### Database Not Creating
- Ensure `/database` directory exists and is writable
- Check PHP error logs for PDOException errors
- Verify PHP PDO SQLite extension is enabled

### "No students found"
- Ensure students are loaded from your main database
- Check SQL query in `mark_attendance.php`
- Verify student table names and column names

### Session Not Persisting
- Ensure `session_start()` is called at the beginning of all files
- Check `session['role']` value for admin access
- Verify `session['student_id']` for student access

## 📝 Notes

- Attendance dates cannot be set in the future (prevents accidental future entries)
- Each student can only have one attendance record per date (UNIQUE constraint)
- Marking timestamp is automatically recorded
- All queries use prepared statements to prevent SQL injection

## 🆘 Support

For issues or customizations:
1. Check the error logs
2. Verify database file exists: `database/attendance.db`
3. Ensure all PHP files have correct include path for `sqlite_config.php`
4. Test database connection by visiting any page

## ✅ Installation Checklist

- [ ] Database directory created (`/database`)
- [ ] All PHP files in `/attendence` folder
- [ ] `sqlite_config.php` configured
- [ ] Session handling enabled in main app
- [ ] Student data source updated (if different from example)
- [ ] Tested admin attendance marking
- [ ] Tested student attendance viewing
- [ ] Mobile responsiveness verified

---

**Version**: 1.0  
**Last Updated**: 2026  
**Database**: SQLite  
