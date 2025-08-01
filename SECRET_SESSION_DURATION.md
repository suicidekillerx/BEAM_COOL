# Secret Page Session Duration

## Current Duration: 24 Hours

The secret collection page currently stays open for **24 hours** after a user enters a valid password.

## How to Change the Duration

### Option 1: Through Admin Panel (Recommended)

1. Go to **Admin Panel** → **Settings** → **Site Settings**
2. Find **"Secret Collection Session Duration"**
3. Select your desired duration:
   - 1 Hour
   - 2 Hours  
   - 6 Hours
   - 12 Hours
   - 24 Hours (Default)
   - 48 Hours
   - 7 Days
4. Click **"Save Settings"**

### Option 2: Direct Code Edit

**File:** `secret-collection.php`  
**Line:** 76-78

Change this line:
```php
$sessionDurationHours = getSiteSetting('secret_session_duration', '24');
```

To a different value:
```php
$sessionDurationHours = '12'; // For 12 hours
$sessionDurationHours = '48'; // For 48 hours
$sessionDurationHours = '168'; // For 7 days
```

## How It Works

1. **User enters password** → Session is created
2. **Session expires** → User must enter password again
3. **IP-based restoration** → If user has recent session from same IP, access is restored
4. **Manual logout** → User can logout anytime via logout button

## Session Features

✅ **Automatic expiration** - Sessions expire after set duration
✅ **IP-based restoration** - Users can regain access from same device
✅ **Manual logout** - Users can logout anytime
✅ **Individual tracking** - Each password usage is tracked separately
✅ **Admin control** - Duration can be changed from admin panel

## Security Notes

- Sessions are tied to both session ID and IP address
- Expired sessions are automatically cleared
- Users can manually logout to end session immediately
- Admin can clear all sessions from Password Management page 