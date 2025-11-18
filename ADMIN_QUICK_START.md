# Guest Submission Workflow - Admin Quick Start Guide

## 🚀 Installation (One-Time Setup)

### 1. Install Database Tables

Open PowerShell/Terminal and run:

```bash
cd d:\projects\stj.fstu.uz
php tools/install_guest_submissions.php
```

You should see:
```
✓ Created table: guest_submissions
✓ Created table: guest_submission_authors
✓ Created table: guest_submission_log
✅ Installation complete!
```

### 2. Create Files Directory

```bash
mkdir files/guest_submissions
```

### 3. Clear Cache

```bash
php tools/clearCache.php
```

Or visit: `https://stj.fstu.uz/itj/guest/clear-cache`

## ✅ You're Ready!

---

## 📊 Accessing the Dashboard

**URL:** `https://stj.fstu.uz/itj/guest/admin`

**Requirements:** You must be logged in as an admin or manager

### What You'll See:

1. **Statistics Cards** - Total submissions, pending, assigned, accepted, rejected
2. **Submissions Table** - All guest submissions with filters
3. **Quick Actions** - View button for each submission

---

## 📝 Managing a Submission

### Step 1: View Submission

1. Go to dashboard: `https://stj.fstu.uz/itj/guest/admin`
2. Click **"View"** button on any submission
3. You'll see:
   - Full submission details
   - Author information
   - Abstract and keywords
   - Activity log
   - Action panels (right side)

### Step 2: Download Manuscript

- Click the **"Download"** button at the top of the right panel
- The manuscript file will download with its original name

### Step 3: Assign to Editor (Optional)

1. In the **"Assign to Editor"** panel:
   - Select an editor from the dropdown
   - Add optional notes for the editor
   - Click **"Assign Submission"**

2. What happens:
   - Status changes to "Assigned"
   - Editor receives email notification
   - Activity logged

### Step 4: Make Decision

1. In the **"Make Decision"** panel:
   - Select decision: **Accept**, **Reject**, or **Revision Required**
   - Add feedback notes for the author (required)
   - Click **"Submit Decision"**

2. What happens:
   - Author receives email with your decision and notes
   - Status automatically updates
   - Activity logged

---

## 🔄 Common Workflows

### Workflow 1: Quick Accept/Reject

```
1. View submission
2. Download and review manuscript
3. Make decision → Select "Accept" or "Reject"
4. Add notes → "Excellent work!" or "Not within scope"
5. Submit → Author gets email
```

### Workflow 2: Editor Review

```
1. View submission
2. Assign to Editor → Select editor, add notes
3. Editor reviews (they can access via admin dashboard)
4. Based on editor feedback, make decision
5. Author gets email with decision
```

### Workflow 3: Track Progress

```
1. View submission
2. Update Status → Select "Under Review"
3. Later, update to "Accepted" or other status
4. Make final decision when ready
```

---

## 📧 Email Notifications

### Automatic Emails Sent:

| Event | Recipient | Contains |
|-------|-----------|----------|
| New submission | Admin | Full details + manuscript file |
| New submission | Author | Confirmation message |
| Assigned to editor | Editor | Submission details + link |
| Decision made | Author | Decision + feedback notes |

### Email Configuration

Emails use SMTP settings from `config.inc.php`:
- Host: `fstu.uz`
- Port: `465`
- Username: `stj_info@fstu.uz`

---

## 🎯 Quick Reference

### Status Meanings

| Status | Meaning |
|--------|---------|
| **Pending** | Just submitted, needs review |
| **Assigned** | Assigned to an editor |
| **Under Review** | Currently being reviewed |
| **Accepted** | Approved for publication |
| **Rejected** | Not suitable for publication |
| **Revision Required** | Needs changes from author |

### Decision Options

| Decision | Effect | Author Email |
|----------|--------|--------------|
| **Accept** | Status → Accepted | Green, positive message |
| **Reject** | Status → Rejected | Red, rejection notice |
| **Revision Required** | Status → Revision Required | Orange, feedback for changes |

---

## 🛠️ Admin Actions Available

### On Dashboard Page (`/guest/admin`)
- ✅ View statistics
- ✅ Filter submissions by status
- ✅ Click to view any submission

### On Submission View Page (`/guest/admin/view/{id}`)
- ✅ Download manuscript file
- ✅ Assign to editor
- ✅ Make decision (Accept/Reject/Revision)
- ✅ Update status manually
- ✅ Add internal admin notes
- ✅ Delete submission
- ✅ View activity log

---

## 💡 Tips & Best Practices

### 1. Review Before Deciding
- Always download and read the manuscript
- Check author qualifications
- Verify it fits journal scope

### 2. Provide Clear Feedback
- Be specific in decision notes
- If rejecting, explain why
- If requesting revision, list specific changes needed

### 3. Use Editor Assignment
- For complex submissions, assign to subject expert
- Add notes to guide the editor
- Editor can access via admin dashboard if they have manager role

### 4. Track with Status Updates
- Use "Under Review" when actively reviewing
- Update status as process progresses
- Helps track workflow at a glance

### 5. Use Admin Notes
- Add internal notes for your records
- Track discussions or decisions
- Not visible to authors

---

## 🚨 Troubleshooting

### Can't Access Dashboard
- **Problem:** "Access denied" or redirect to login
- **Solution:** Ensure you're logged in with admin or manager role

### Submission Not Appearing
- **Problem:** Author submitted but not in dashboard
- **Solution:** 
  1. Check if database tables exist
  2. Clear cache: `php tools/clearCache.php`
  3. Check error logs

### Email Not Sending
- **Problem:** Decision made but author didn't receive email
- **Solution:**
  1. Check SMTP settings in `config.inc.php`
  2. Test email: `/itj/guest/testmail`
  3. Check spam folder

### File Won't Download
- **Problem:** Download button doesn't work
- **Solution:**
  1. Check file exists in `files/guest_submissions/`
  2. Verify file permissions (should be readable)
  3. Check browser console for errors

---

## 📞 Need Help?

1. **Read the full guide:** `GUEST_SUBMISSION_WORKFLOW_GUIDE.md`
2. **Check error logs:** `logs/email-errors.log`
3. **Test the system:** Submit a test manuscript via guest form
4. **Clear cache:** Often solves display issues

---

## 🎉 That's It!

You now have a complete workflow system for managing guest submissions. The system will:

✅ Store all submissions in database  
✅ Let you assign to editors  
✅ Send automated emails  
✅ Track the complete lifecycle  
✅ Provide a clean admin interface  

**Start managing submissions at:** `https://stj.fstu.uz/itj/guest/admin`

---

**Questions?** Check the full documentation in `GUEST_SUBMISSION_WORKFLOW_GUIDE.md`

