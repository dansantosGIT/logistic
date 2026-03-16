# Running Laravel Scheduler on Windows

This project uses a scheduled Artisan command (`reminders:send-expected-returns`) registered in `app/Console/Kernel.php`.

On Linux you'd normally run `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1` via cron. On Windows use Task Scheduler.

Quick (PowerShell) helper

1. Open PowerShell as Administrator.
2. Run the helper script shipped with the project (adjust `-PhpPath` and `-AppPath` if needed):

```powershell
# from the project root
.\scripts\create_schtask.ps1 -PhpPath 'C:\laragon\bin\php\php-8.2\php.exe' -AppPath 'C:\laragon\www\logistic'
```

What the script does

- Detects `php` if you omit `-PhpPath` (requires `php` on PATH), validates `artisan` in `-AppPath`.
- Creates/updates a scheduled task named `Logistic_Laravel_Scheduler` to run every minute as `SYSTEM`.
- Appends `php artisan schedule:run` output to `storage/logs/schedule.log` for debugging.

Security/production notes

- Running tasks as `SYSTEM` is simple for local/dev hosts. For production consider a dedicated service account and set appropriate file permissions.
- Ensure your `.env` `MAIL_*` settings are production ready; during testing you can set `MAIL_FORCE_LOG=true` to avoid SMTP issues and log sends instead.
