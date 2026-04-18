# 👥 Attendance System - Quick User Guide

## 🎓 For Admins/Staff: How to Mark Attendance

### Step-by-Step Guide

#### 1. **Open Mark Attendance Page**
   ```
   Go to: attendence/mark_attendance.php
   ```

#### 2. **Select Date**
   - Click the date picker
   - Choose the date to mark attendance for
   - Cannot select future dates (automatic restriction)
   - Click "Search" or date will auto-load

#### 3. **Mark Attendance**
   
   **Option A: Individual Marking**
   - Find each student in the table
   - Change their status dropdown:
     - ✓ **Present** (Green)
     - ✗ **Absent** (Red)
     - ⏸ **Leave** (Yellow)
   - Add optional remarks in the text field
   - Repeat for each student

   **Option B: Bulk Marking**
   - Use the button bar at the top:
     - ☑️ "Select All" - Check all students
     - "Mark All Present" - All selected = Present
     - "Mark All Absent" - All selected = Absent
     - "Mark All Leave" - All selected = Leave

#### 4. **Check Summary**
   - See real-time count:
     - Total Students
     - Present count
     - Absent count
     - Leave count

#### 5. **Save**
   - Click "Save Attendance" button
   - Page redirects with confirmation message
   - Data saved to database with timestamp

---

## 📊 For Admins: How to View Records

### Step-by-Step Guide

#### 1. **Open View Attendance Page**
   ```
   Go to: attendence/view_attendance.php
   ```

#### 2. **View Statistics**
   - **Top dashboard shows**:
     - Total records marked
     - Present count
     - Absent count
     - Leave count
     - Number of students

#### 3. **Filter by Month**
   - Select month picker: "Month"
   - Automatically updates statistics and records
   - Shows last 500 records

#### 4. **View Records Table**
   - Date of attendance
   - Student enrollment ID
   - Student name
   - Status (color-coded badge)
   - Any remarks added
   - Timestamp when marked

#### 5. **Export Data** (if enabled)
   - Click "Export CSV" button
   - Downloads attendance data as CSV file
   - Can open in Excel

---

## 👨‍🎓 For Students: How to Check Attendance

### Step-by-Step Guide

#### 1. **Open My Attendance Page**
   ```
   Go to: attendence/student_attendance.php
   ```

#### 2. **View Current Month Summary**
   - **See statistics for current month**:
     - Total days marked
     - Days present
     - Days absent
     - Days leave
     - Attendance percentage (%)
   - **Progress bar** shows visual representation

#### 3. **View Overall Summary**
   - Scroll down to see **all-time statistics**
   - Total days since enrollment
   - Total present days
   - Overall attendance percentage

#### 4. **Select Different Month**
   - Use month picker at top
   - Changes calendar and records
   - Updates statistics

#### 5. **View Calendar**
   - **Interactive calendar shows**:
     - 🟢 Green = Present
     - 🔴 Red = Absent
     - 🟡 Yellow = Leave
     - White = No record yet
   - Hover over dates for details

#### 6. **View Detailed Records**
   - Scroll to bottom for table
   - Shows **all attendance entries**:
     - Date (with day of week)
     - Status
     - Any remarks from staff
     - When it was marked

---

## ⌨️ Keyboard Shortcuts & Tips

### Mark Attendance Page
- **Tab key**: Navigate between fields
- **Space**: Check/uncheck checkboxes
- **Enter**: Submit form

### View Attendance Page
- **Ctrl+F**: Search within page
- **Click date header**: May sort (if implemented)

---

## 🎨 Understanding the Colors

### Status Colors

| Color | Status | Meaning |
|-------|--------|---------|
| 🟢 Green | Present | Student was present |
| 🔴 Red | Absent | Student was absent |
| 🟡 Yellow | Leave | Student had authorized leave |
| ⚪ White | No Record | Not marked yet |

### Interface Colors

| Color | Meaning |
|-------|---------|
| Purple (#667eea) | Primary buttons & headers |
| Green (#10b981) | Success actions |
| Red (#ef4444) | Absence/danger |
| Gray (#999) | Secondary buttons |

---

## 📱 Mobile Tips

### On Your Phone
- ✅ All features work on mobile
- 📊 Tables scroll horizontally if needed
- 👆 Tap buttons instead of clicking
- 📅 Date picker works on mobile

### Landscape Mode
- Better for viewing tables
- More space for options
- Easier data entry

---

## ❓ Frequently Asked Questions

### For Admins

**Q: Can I mark attendance for past dates?**
A: Yes! Select any past date. Future dates are blocked automatically.

**Q: What if I mark wrong?**
A: You can visit again, select same date, and update. Changes overwrite previous records.

**Q: Can I add notes?**
A: Yes! In the "Remarks" field for each student. These show up in records.

**Q: How do I bulk mark?**
A: Use "Mark All Present/Absent/Leave" buttons at top. Or select specific students first.

**Q: Where is the data saved?**
A: SQLite database at `database/attendance.db`

### For Students

**Q: Why is my attendance not showing?**
A: Staff hasn't marked it yet. Check back after marking date.

**Q: What do the calendar colors mean?**
A: Green=Present, Red=Absent, Yellow=Leave, White=Not marked

**Q: Can I change my attendance?**
A: No, only staff can mark/change. Contact your teacher/admin.

**Q: How is percentage calculated?**
A: (Days Present) ÷ (Total Days) × 100

**Q: What's the difference between "Current Month" and "Overall"?**
A: Current Month = This calendar month only. Overall = Since enrollment.

---

## ⚠️ Important Notes

### ✅ Do's
- ✓ Mark attendance daily
- ✓ Add notes for special cases (leave, late arrival, etc.)
- ✓ Check month filter when viewing records
- ✓ Verify data before saving

### ❌ Don'ts
- ✗ Don't mark future dates (system prevents this)
- ✗ Don't mark without verification
- ✗ Don't forget to click "Save" button
- ✗ Don't share login credentials

---

## 🆘 Troubleshooting

### "No students found"
- Check if date is selected
- Try clicking "Search" button
- Verify students are in system

### "Database not working"
- Refresh the page
- Check if `/database` folder exists
- Clear browser cache

### "Can't save attendance"
- Make sure you selected at least one student status
- Check if all required fields are filled
- Try again or contact admin

### Mobile page looks weird
- Try rotating to landscape mode
- Refresh the page
- Update browser

---

## 📞 Getting Help

**Contact Your Administrator:**
- For attendance disputes
- To mark historical attendance
- If system has issues
- For any access problems

**Check Documentation:**
- See `SETUP_GUIDE.md` for technical details
- See `CHANGES_SUMMARY.md` for what's new

---

## 💡 Pro Tips

### For Efficient Marking
1. Mark attendance at same time every day
2. Batch students by batch/class
3. Use "Mark All" for quick bulk marking
4. Add remarks for absences

### For Checking Attendance
1. Check calendar view first (quick visual)
2. Use month filter to navigate
3. Note attendance percentage for patterns
4. Contact admin for questions

### For Best Experience
- Use on desktop for marking (easier)
- Use on mobile for checking (portable)
- Use recent browser (Chrome/Firefox recommended)
- Clear cache if issues occur

---

## 📈 Understanding Reports

### Statistics Dashboard
Shows:
- **Total Records**: How many attendance entries marked
- **Present**: Students marked present
- **Absent**: Students marked absent
- **Leave**: Students marked on leave
- **Students**: How many unique students

### Student Summary
Shows for **current month**:
- **Total Days**: Days marked (Present+Absent+Leave)
- **Present**: Days student was present
- **Absent**: Days student was absent
- **Leave**: Approved leave days
- **Percentage**: Attendance % = (Present/Total) × 100

---

## 🎯 Best Practices

### Marking Attendance
1. **Before marking**: Verify student list is correct
2. **During marking**: Check names carefully
3. **After marking**: Review summary counts
4. **When errors found**: Contact admin to correct

### Checking Attendance
1. **Monitor regularly**: Check own attendance weekly
2. **Keep track**: Note any discrepancies
3. **Ask questions**: If percentage seems wrong
4. **Plan ahead**: If leaving, inform staff

---

**Last Updated**: April 18, 2026  
**System Version**: 1.0  
**Support**: Contact your administrator

