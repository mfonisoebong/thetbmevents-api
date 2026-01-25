<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>Welcome to TBM Events</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;">
<!-- Preheader (hidden preview text) -->
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Welcome to TBM Events — your attendee account is ready.
</div>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F8FAFC;">
    <tr>
        <td align="center" style="padding:32px 12px;">
            <!-- Container -->
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
                                                    Tickets • Events • Experiences
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="right" style="padding:0 4px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#9CA3AF;">
                                        {{ now()->format('F j, Y') }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Card -->
                <tr>
                    <td style="background-color:#FFFFFF;border:1px solid rgba(15,23,42,0.08);border-radius:4px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.06);">
                        <!-- Accent header -->
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:18px 22px;background:#E8B025;">
                                    <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:800;color:#0F172A;">
                                        Welcome, {{ $user->full_name }}.
                                    </div>
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(15,23,42,0.9);margin-top:4px;">
                                        Your attendee account is ready. Let’s find your next event.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:22px 22px 10px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:22px;color:#111827;">
                                        Hi {{ $user->full_name }},
                                        <br /><br />
                                        Thanks for signing up on <strong>TBM Events</strong>. You can now explore events, book tickets, and keep everything in one place.
                                    </div>
                                </td>
                            </tr>

                            <!-- Quick features -->
                            <tr>
                                <td style="padding:0 22px 16px 22px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td style="padding:10px 12px;border:1px solid rgba(15,23,42,0.08);border-radius:12px;" width="45%">
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#0F172A;">Discover events</div>
                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#4B5563;margin-top:4px;">
                                                    Browse what’s happening near you and online.
                                                </div>
                                            </td>
                                            <td width="5%" style="font-size:0;line-height:0;">&nbsp;</td>
                                            <td style="padding:10px 12px;border:1px solid rgba(15,23,42,0.08);border-radius:12px;" width="45%">
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#0F172A;">Manage tickets</div>
                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#4B5563;margin-top:4px;">
                                                    Keep receipts and entry details organized.
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- CTA -->
                            <tr>
                                <td style="padding:4px 22px 6px 22px;">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td align="center" style="border-radius:999px;background:#E8B025;">
                                                <a
                                                    href="{{config('app.client_url') . '/explore'}}"
                                                    style="display:inline-block;padding:12px 18px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:18px;font-weight:800;color:#0F172A;text-decoration:none;border-radius:999px;"
                                                >Explore events</a
                                                >
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:10px 22px 18px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;">
                                        Tip: Save time at checkout by completing your profile.
                                        {{--<a href="#" style="color:#4F8A92;text-decoration:none;font-weight:700;">Update profile →</a>--}}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:0 22px 22px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;">
                                        If you didn’t create this account, please ignore this email or contact us at
                                        <a href="mailto:{{ config('mail.support_email') }}" style="color:#4F8A92;text-decoration:none;font-weight:700;">{{ config('mail.support_email') }}</a>.
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
                            <br />
                            <span style="color:#9CA3AF;">5, FUNSO FABURE CLOSE, ADO-ODO OTA, OGUN STATE, NIGERIA</span>
                        </div>
                    </td>
                </tr>
            </table>
            <!-- /Container -->
        </td>
    </tr>
</table>
</body>
</html>
