# 🚀 ATTENDANCE SYSTEM - QUICK REFERENCE

## 📌 Pages at a Glance

| Role | Purpose | URL |
|------|---------|-----|
| **Admin** | Mark attendance | `attendence/mark_attendance.php` |
| **Admin** | View records | `attendence/view_attendance.php` |
| **Student** | Check my attendance | `attendence/student_attendance.php` |

---

## 🎯 Mark Attendance (Admin)

```
1. Open mark_attendance.php
2. Select DATE
3. Mark students:
   ✓ Green = Present
   ✗ Red = Absent  
   ⏸ Yellow = Leave
4. Add remarks (optional)
5. Click SAVE
```

**Bulk Actions:**
- Mark All Present
- Mark All Absent
- Mark All Leave

---

## 📊 View Records (Admin)

```
1. Open view_attendance.php
2. Select MONTH
3. See statistics:
   - Total marked
   - Present count
   - Absent count
   - Leave count
4. Browse records table
```

---

## 👨‍🎓 Check Attendance (Student)

```
1. Open student_attendance.php
2. View CALENDAR:
   🟢 Green = Present
   🔴 Red = Absent
   🟡 Yellow = Leave
3. Check STATISTICS:
   - Current month %
   - Overall %
4. Review detailed records table
```

---

## 🗄️ Database Info

**File**: `database/attendance.db`  
**Type**: SQLite  
**Status Options**: Present, Absent, Leave  
**Auto-creates**: On first visit  

---

## 🔐 Access Requirements

| Page | Requirement |
|------|-------------|
| mark_attendance.php | Session['role'] = 'admin' or 'staff' |
| view_attendance.php | Session['role'] = 'admin' or 'staff' |
| student_attendance.php | Session['student_id'] set |

---

## 📱 Colors

```
🟢 Present  = #10b981 (Green)
🔴 Absent   = #ef4444 (Red)
🟡 Leave    = #f59e0b (Orange)
💜 Primary  = #667eea (Purple)
```

---

## ⚡ Key Features

✅ Real-time summaries  
✅ Bulk actions  
✅ Remarks/notes per student  
✅ Calendar view  
✅ Progress bars  
✅ Mobile responsive  
✅ Automatic timestamps  
✅ Transaction support  

---

## 🆘 Quick Fixes

**Database not creating?**
```
Check: /database directory exists and is writable
Chmod: 755 on /database folder
```

**Can't save?**
```
Check: At least one student selected
Check: Date field has value
Check: No future dates
```

**Students not showing?**
```
Check: Mark attendance page date selected
Check: Student data source configured
Check: Database query updated
```

---

## 📝 Files Overview

| File | Purpose |
|------|---------|
| `sqlite_config.php` | Database connection & init |
| `mark_attendance.php` | Mark attendance form |
| `save_attendance.php` | Process submission |
| `view_attendance.php` | View records |
| `student_attendance.php` | Student dashboard |
| `USER_GUIDE.md` | Full user guide |
| `SETUP_GUIDE.md` | Setup instructions |

---

## 🔄 Workflow

### For Admin
```
Select Date → Load Students → Mark Status → Save → Done
```

### For Student  
```
Open Dashboard → View Calendar → Check Stats → Read Records
```

---

## 📊 Example: Mark Daily Attendance

```bash
Time: 09:00 AM
1. Open: mark_attendance.php
2. Date: Today (auto-selected)
3. Bulk: Click "Mark All Present"
4. Find: John Smith (Absent)
   - Change to "Absent"
   - Remark: "Medical leave"
5. Find: Jane Doe (Late)
   - Mark "Present"
   - Remark: "Arrived late at 9:30"
6. Save: Click Save button
✓ Done! 45/46 marked present
```

---

## 📈 Example: Check Attendance

```bash
Student Login → Student Attendance Page
View Calendar (April 2026)
  🟢 Present: 18 days
  🔴 Absent: 2 days
  🟡 Leave: 1 day
  📊 Percentage: 88.3%
Details Table: Shows each day
```

---

## ✅ Verification Checklist

After setup, verify:

- [ ] Database file exists: `database/attendance.db`
- [ ] mark_attendance.php opens without errors
- [ ] Can select date and see students (if students in DB)
- [ ] Can change status dropdowns
- [ ] Save button works (check redirects)
- [ ] view_attendance.php shows statistics
- [ ] student_attendance.php shows calendar
- [ ] Mobile view is responsive
- [ ] No browser console errors

---

## 🎨 Customization Points

Want to change something? Edit:

**Colors**: `<style>` section in each PHP file
**Status options**: Select fields in forms (Present/Absent/Leave)
**Fields**: SQL tables in `sqlite_config.php`
**Messages**: Session messages in `save_attendance.php`

---

## 📞 Need Help?

1. **Check**: SETUP_GUIDE.md (full technical docs)
2. **Check**: USER_GUIDE.md (detailed usage)
3. **Check**: CHANGES_SUMMARY.md (what's new)
4. **Debug**: Enable error logs in PHP
5. **Contact**: System administrator

---

## 🌐 Browser Support

| Browser | Status |
|---------|--------|
| Chrome | ✅ Full support |
| Firefox | ✅ Full support |
| Safari | ✅ Full support |
| Edge | ✅ Full support |
| IE 11 | ⚠️ Limited |
| Mobile | ✅ Responsive |

---

## ⚙️ Technical Specs

```
Backend:     PHP 7.0+
Database:    SQLite3
Frontend:    HTML5 + CSS3 + Vanilla JS
Architecture: Lightweight, no frameworks
Performance: Indexed queries
Security:    Prepared statements, XSS protection
```

---

## 💾 Database Schema (Simple)

```sql
attendance
├── id (PRIMARY KEY)
├── student_id
├── student_name
├── enrollment_id
├── attendance_date (DATE)
├── status (Present/Absent/Leave)
├── remarks
├── marked_by
├── marked_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

---

**Print this page for quick reference!**

Last Updated: April 18, 2026  
Version: 1.0

