# Guest Submission Workflow System - Complete Guide

## Overview

This system provides a complete workflow for managing guest submissions in OJS, allowing admins to:
- View all guest submissions in a dashboard
- Assign submissions to editors for review
- Make decisions (Accept/Reject/Revision Required)
- Send automated email notifications to authors and editors
- Track the complete submission lifecycle

## Installation

### Step 1: Install Database Schema

Run the installation script to create the required database tables:

```bash
cd d:\projects\stj.fstu.uz
php tools/install_guest_submissions.php
```

This will create three tables:
- `guest_submissions` - Stores submission data
- `guest_submission_authors` - Stores author information
- `guest_submission_log` - Tracks all actions and changes

### Step 2: Verify File Permissions

Ensure the files directory exists and is writable:

```bash
mkdir -p files/guest_submissions
chmod 755 files/guest_submissions
```

### Step 3: Clear Cache

Clear OJS cache to load new classes:

```bash
php tools/clearCache.php
```

Or visit: `https://stj.fstu.uz/itj/guest/clear-cache`

## Features

### 1. Automated Submission Storage

When a guest submits a manuscript:
- ✅ All data is saved to the database
- ✅ Manuscript file is stored securely
- ✅ Authors are linked to the submission
- ✅ Activity log is created
- ✅ Emails are sent to admin and author

### 2. Admin Dashboard

**URL:** `https://stj.fstu.uz/itj/guest/admin`

**Features:**
- View statistics (total, pending, assigned, accepted, rejected, etc.)
- See all submissions in a sortable table
- Filter by status
- Quick access to view/manage each submission

**Access:** Requires admin or manager role

### 3. Submission Management

**URL:** `https://stj.fstu.uz/itj/guest/admin/view/{submission_id}`

**Actions Available:**

#### a) Assign to Editor
- Select an editor from dropdown
- Add optional notes for the editor
- Editor receives email notification with link to submission
- Status automatically changes to "Assigned"

#### b) Make Decision
- Choose: Accept, Reject, or Revision Required
- Provide feedback notes for the author
- Author receives email notification with decision
- Status automatically updates based on decision

#### c) Update Status
- Manually change submission status
- Options: Pending, Assigned, Under Review, Accepted, Rejected, Revision Required
- Useful for tracking workflow progress

#### d) Admin Notes
- Add internal notes (not visible to authors)
- Track internal discussions or reminders

#### e) Download Manuscript
- Download the submitted manuscript file
- Original filename preserved

#### f) Delete Submission
- Permanently delete submission
- Removes all associated data and files

### 4. Activity Logging

Every action is logged with:
- Timestamp
- User who performed the action
- Action type
- Detailed message

Visible in the submission view page.

## Workflow Examples

### Example 1: Standard Review Process

1. **Author submits manuscript** via guest form
   - Submission saved to database
   - Admin receives email notification
   - Author receives confirmation email

2. **Admin reviews submission** at `/guest/admin`
   - Views submission details
   - Downloads and reviews manuscript

3. **Admin assigns to editor**
   - Selects editor from dropdown
   - Adds notes: "Please review for technical accuracy"
   - Editor receives email with link to submission

4. **Editor reviews** (can access via admin dashboard if they have manager role)
   - Downloads manuscript
   - Reviews content

5. **Admin makes decision** based on editor's feedback
   - Selects "Accept" or "Reject" or "Revision Required"
   - Adds feedback for author
   - Author receives email notification

6. **Submission complete**
   - If accepted: Admin can manually create official OJS submission
   - If rejected: Process ends
   - If revision required: Author can resubmit via guest form

### Example 2: Quick Rejection

1. Author submits manuscript
2. Admin reviews at `/guest/admin/view/{id}`
3. Admin immediately makes decision: "Reject"
4. Adds notes: "Topic not within journal scope"
5. Author receives rejection email
6. Status updated to "Rejected"

### Example 3: Multiple Editors

1. Author submits manuscript
2. Admin assigns to Editor A for initial review
3. Editor A reviews, provides feedback
4. Admin reassigns to Editor B for second opinion
5. Editor B reviews
6. Admin makes final decision based on both reviews

## Email Notifications

### 1. Submission Received (to Admin)
- **Trigger:** New guest submission
- **Recipient:** Admin email (defined in config)
- **Content:** Full submission details, author info, link to admin dashboard
- **Attachment:** Manuscript file

### 2. Submission Confirmation (to Author)
- **Trigger:** New guest submission
- **Recipient:** Submitting author
- **Content:** Confirmation message, submission details

### 3. Assignment Notification (to Editor)
- **Trigger:** Submission assigned to editor
- **Recipient:** Assigned editor
- **Content:** Submission details, link to view submission, admin notes

### 4. Decision Notification (to Author)
- **Trigger:** Decision made on submission
- **Recipient:** Primary author
- **Content:** Decision (Accept/Reject/Revision), feedback notes
- **Color-coded:** Green for accept, red for reject, orange for revision

## API Endpoints

All admin operations use AJAX endpoints:

### Assign Submission
```
POST /itj/guest/admin/assign
Parameters:
  - submission_id: int
  - editor_id: int
  - notes: string (optional)
```

### Make Decision
```
POST /itj/guest/admin/decide
Parameters:
  - submission_id: int
  - decision: string (accept|reject|revision_required)
  - notes: string (required)
```

### Update Status
```
POST /itj/guest/admin/updateStatus
Parameters:
  - submission_id: int
  - status: string
```

### Update Notes
```
POST /itj/guest/admin/updateNotes
Parameters:
  - submission_id: int
  - notes: string
```

### Delete Submission
```
POST /itj/guest/admin/delete
Parameters:
  - submission_id: int
```

### Download File
```
GET /itj/guest/admin/download/{submission_id}
```

## Database Schema

### guest_submissions
- `submission_id` - Primary key
- `context_id` - Journal context
- `manuscript_title` - Title
- `article_type` - Type of article
- `abstract` - Abstract text
- `keywords` - Keywords (semicolon separated)
- `manuscript_file_path` - Server path to file
- `manuscript_file_name` - Original filename
- `status` - Current status
- `assigned_editor_id` - Assigned editor (nullable)
- `decision` - Final decision (nullable)
- `decision_notes` - Feedback for author
- `admin_notes` - Internal notes
- `date_submitted` - Submission timestamp
- `date_assigned` - Assignment timestamp
- `date_decided` - Decision timestamp

### guest_submission_authors
- `author_id` - Primary key
- `submission_id` - Foreign key
- `title` - Dr., Prof., etc.
- `first_name` - First name
- `last_name` - Last name
- `authorship` - First Author, Co-Author, etc.
- `email` - Email address
- `address` - Full address
- `affiliation` - Institution
- `seq` - Author order

### guest_submission_log
- `log_id` - Primary key
- `submission_id` - Foreign key
- `user_id` - User who performed action
- `action` - Action type
- `message` - Detailed message
- `date_logged` - Timestamp

## Status Values

- `pending` - Newly submitted, awaiting review
- `assigned` - Assigned to an editor
- `under_review` - Currently being reviewed
- `accepted` - Accepted for publication
- `rejected` - Rejected
- `revision_required` - Needs revision from author

## Security

### Access Control
- Admin dashboard requires admin or manager role
- Uses OJS's built-in authorization system
- All operations check user permissions

### File Security
- Files stored outside web root
- Access only via authenticated download endpoint
- File type validation on upload
- Size limits enforced

### Data Validation
- All inputs sanitized
- SQL injection prevention via Eloquent ORM
- XSS protection in templates

## Customization

### Email Templates

Edit email templates in `GuestAdminHandler.php`:
- `getAssignmentEmailBody()` - Editor notification
- `getDecisionEmailBody()` - Author decision notification

### SMTP Configuration

Update in `GuestAdminHandler.php` (lines 373-383):
```php
$mail->Host = 'fstu.uz';
$mail->Port = 465;
$mail->Username = 'stj_info@fstu.uz';
$mail->Password = '7san3_9I3';
```

### Status Options

Add/modify status values in `GuestSubmission.php`:
```php
const STATUS_PENDING = 'pending';
const STATUS_ASSIGNED = 'assigned';
// Add more as needed
```

## Troubleshooting

### Submissions not appearing in dashboard
1. Check database tables exist: `SHOW TABLES LIKE 'guest_%';`
2. Verify submission was saved: Check `guest_submissions` table
3. Clear cache: `php tools/clearCache.php`

### Email not sending
1. Check SMTP credentials in `GuestAdminHandler.php`
2. Test email separately: `/itj/guest/testmail`
3. Check error logs: `logs/email-errors.log`

### Permission denied errors
1. Verify user has admin or manager role
2. Check file permissions: `files/guest_submissions/` should be 755
3. Clear sessions and re-login

### File download not working
1. Check file exists: `files/guest_submissions/`
2. Verify file path in database
3. Check web server has read permissions

## Integration with OJS

### Converting to Official Submission

Once a guest submission is accepted, admin can manually create an official OJS submission:

1. Go to OJS dashboard → Submissions → New Submission
2. Use "Submit on behalf of" feature
3. Copy author information from guest submission
4. Upload the manuscript file (download from guest system)
5. Copy title, abstract, keywords
6. Complete the 5-step submission process
7. Mark guest submission as "Accepted" in guest system

### Future Enhancements

Possible improvements:
- Automatic conversion to OJS submission
- Author portal for tracking submission status
- Reviewer assignment from guest system
- Integration with OJS review workflow
- Bulk operations (assign multiple, export data)
- Advanced filtering and search
- Email template customization via admin interface

## Support

For issues or questions:
1. Check this guide
2. Review error logs
3. Test with a fresh submission
4. Verify database schema is correct

---

**Version:** 1.0  
**Date:** November 18, 2025  
**Compatible with:** OJS 3.x

