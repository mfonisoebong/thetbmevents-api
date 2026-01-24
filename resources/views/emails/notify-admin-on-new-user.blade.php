<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>New Organizer Signup</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    A new organizer has signed up on TBM Events.
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
                                                    Admin Alert
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="right" style="padding:0 4px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#9CA3AF;">
                                        {{ date('Y-m-d H:i:s') }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="background-color:#FFFFFF;border:1px solid rgba(15,23,42,0.08);border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.06);">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:18px 22px;background:#E8B025;">
                                    <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:800;color:#0F172A;">
                                        New organizer signup
                                    </div>
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(15,23,42,0.9);margin-top:4px;">
                                        Review the details and approve if required.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:18px 22px 10px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:22px;color:#111827;">
                                        A new organizer has just created an account on <strong>TBM Events</strong>.
                                    </div>
                                </td>
                            </tr>

                            <!-- Details -->
                            <tr>
                                <td style="padding:0 22px 16px 22px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F9FAFB;border:1px solid rgba(15,23,42,0.06);border-radius:14px;">
                                        <tr>
                                            <td style="padding:14px;">
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#0F172A;margin-bottom:8px;">Organizer details</div>

                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:20px;color:#4B5563;">
                                                    Name: <span style="color:#111827;">{{ $user->full_name }}</span><br />
                                                    Email: <span style="color:#111827;">{{ $user->email }}</span><br />
                                                    Organization: <span style="color:#111827;">{{ $user->business_name }}</span><br />
                                                    Phone: <span style="color:#111827;">{{ $user->phone_number }}</span><br />
                                                    Country: <span style="color:#111827;">{{ $user->country }}</span><br />
                                                </div>

                                                <div style="margin-top:10px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;">
                                                    User ID: <span style="color:#111827;">{{ $user->id }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- CTA -->
                            <tr>
                                <td style="padding:4px 22px 18px 22px;">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            {{--<td align="center" style="border-radius:999px;background:#E8B025;">
                                                <a
                                                    href="{{adminReviewUrl}}"
                                                    style="display:inline-block;padding:12px 18px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:18px;font-weight:800;color:#0F172A;text-decoration:none;border-radius:999px;"
                                                >Review organizer</a
                                                >
                                            </td>--}}
                                            <td width="10" style="font-size:0;line-height:0;">&nbsp;</td>
                                            <td align="center" style="border-radius:999px;background-color:#0F172A;">
                                                <a
                                                    href="{{ config('app.client_url') . '/login' }}"
                                                    style="display:inline-block;padding:12px 16px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:18px;font-weight:700;color:#F8FAFC;text-decoration:none;border-radius:999px;"
                                                >Open admin</a
                                                >
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:0 22px 22px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;">
                                        Security note: if this signup looks suspicious, you can disable the account from the admin panel.
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
                            This is an automated notification from TBM Events.
                            <br />
                            © {{ date('Y') }} TBM Events.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
