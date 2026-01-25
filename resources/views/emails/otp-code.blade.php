@php
    function getOTPActionText($type) : string {
        return match ($type) {
            'email_verification' => 'verify your email address',
            'password_reset' => 'reset your password',
            default => 'proceed with your action',
        };
    }

    $supportEmail = config('mail.support_email')
@endphp

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>Verify your code</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Your TBM Events verification code is {{ $otp->otp }}.
</div>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F8FAFC;">
    <tr>
        <td align="center" style="padding:32px 12px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px;max-width:600px;">
                <!-- Header -->
                <tr>
                    <td style="padding:0 0 16px 0;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td align="left" style="padding:0 4px;">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="vertical-align:middle;padding-right:10px;">
                                                <img
                                                    src="https://server.thetbmevents.com/images/tbm-logo.png"
                                                    width="40"
                                                    height="40"
                                                    alt="TBM Events"
                                                    style="display:block;border:0;outline:none;text-decoration:none;border-radius:10px;"
                                                />
                                            </td>
                                            <td style="vertical-align:middle;">
                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:22px;font-weight:800;color:#0F172A;">
                                                    TBM <span style="color:#E8B025;">EVENTS</span>
                                                </div>
                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#4B5563;">
                                                    Verification
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="right" style="padding:0 4px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#9CA3AF;">
                                        {{ now()->format('F j, Y, g:i a') }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="background-color:#FFFFFF;border:1px solid rgba(15,23,42,0.08);border-radius:4px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.06);">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:18px 22px;background-color:#E8B025;">
                                    <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:800;color:#0F172A;">
                                        Your {{ $otp->type == 'email_verification' ? 'email' : '' }} verification code
                                    </div>
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(15,23,42,0.9);margin-top:4px;">
                                        Use the code below to continue.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:18px 22px 8px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:22px;color:#111827;">
                                        Hi {{ $user->full_name }},
                                        <br /><br />
                                        Enter this one-time code to {{ getOTPActionText($otp->type) }}. This code expires in <strong>30 minutes</strong>.
                                    </div>
                                </td>
                            </tr>

                            <!-- OTP Code block -->
                            <tr>
                                <td style="padding:6px 22px 16px 22px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F9FAFB;border:1px dashed rgba(15,23,42,0.2);border-radius:16px;">
                                        <tr>
                                            <td align="center" style="padding:18px 12px;">
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;font-weight:800;color:#4B5563;letter-spacing:0.12em;text-transform:uppercase;">
                                                    Verification code
                                                </div>
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:34px;line-height:40px;font-weight:800;color:#0F172A;letter-spacing:0.18em;margin-top:8px;">
                                                    {{ $otp->otp }}
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Fallback -->
                            <tr>
                                <td style="padding:0 22px 18px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;">
                                        If you didn’t request this, you can safely ignore this email.
                                        For support, contact <a href="mailto:{{ $supportEmail }}" style="color:#4F8A92;text-decoration:none;font-weight:700;">{{ $supportEmail }}</a>.
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:16px 6px 0 6px;">
                        <div style="text-align:center;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#9CA3AF;">
                            © {{ now()->year }} TBM Events. All rights reserved.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
