@php use Carbon\Carbon; @endphp
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="color-scheme" content="light"/>
    <meta name="supported-color-schemes" content="light"/>
    <title>Account suspended</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;">
<!-- Preheader (hidden preview text) -->
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Your TBM Events account has been temporarily suspended. Review details and next steps.
</div>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F8FAFC;">
    <tr>
        <td align="center" style="padding:32px 12px;">
            <!-- Container -->
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                   style="width:600px;max-width:600px;">
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
                                                <div
                                                    style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:22px;font-weight:800;color:#0F172A;">
                                                    TBM <span style="color:#E8B025;">EVENTS</span>
                                                </div>
                                                <div
                                                    style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#4B5563;">
                                                    Account notice
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="right" style="padding:0 4px;">
                                    <div
                                        style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#9CA3AF;">
                                        {{ Carbon::now()->format('d/m/y H:i:s') }}
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
                                    <div
                                        style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:800;color:#0F172A;">
                                        Your account has been suspended
                                    </div>
                                    <div
                                        style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(15,23,42,0.9);margin-top:4px;">
                                        This is a temporary restriction and may be resolved after review.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:22px 22px 12px 22px;">
                                    <div
                                        style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:22px;color:#111827;">
                                        Hi {{ $user->business_name ?: $user->full_name }},
                                        <br/><br/>
                                        We noticed activity on your <strong>TBM Events</strong> account that requires
                                        attention. As a precaution, we’ve suspended your account.
                                    </div>
                                </td>
                            </tr>

                            <!-- Details -->
                            <tr>
                                <td style="padding:0 22px 16px 22px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                           style="background-color:#F9FAFB;border:1px solid rgba(15,23,42,0.06);border-radius:14px;">
                                        <tr>
                                            <td style="padding:14px 14px 2px 14px;">
                                                <div
                                                    style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#0F172A;">
                                                    Suspension details
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:0 14px 14px 14px;">
                                                <div
                                                    style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:20px;color:#4B5563;">
                                                    Status: <span
                                                        style="color:#111827;font-weight:700;">Suspended</span><br/>
                                                    Effective: <span
                                                        style="color:#111827;">{{ Carbon::now()->format('d/m/y H:i:s') }}</span><br/>
                                                    {{--Reference: <span style="color:#111827;"></span>--}}
                                                    <br/>
                                                    Reason: <span style="color:#111827;">Suspicious Activity</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Next steps -->
                            <tr>
                                <td style="padding:0 22px 10px 22px;">
                                    <div
                                        style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:20px;color:#4B5563;">
                                        If you believe this is a mistake, you can request a review. {{--For faster help,
                                        include your reference ID <strong>{{caseId}}</strong> in your message.--}}
                                    </div>
                                </td>
                            </tr>

                            {{--<!-- CTA -->
                            <tr>
                                <td style="padding:12px 22px 8px 22px;">
                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                        <tr>
                                            <td align="center" style="border-radius:999px;background:#0F172A;">
                                                <a
                                                    href="{{appealUrl}}"
                                                    style="display:inline-block;padding:12px 18px;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:18px;font-weight:800;color:#FFFFFF;text-decoration:none;border-radius:999px;"
                                                >Request review</a
                                                >
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Support note -->
                            <tr>
                                <td style="padding:10px 22px 22px 22px;">
                                    <div
                                        style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;">
                                        If the button doesn’t work, copy and paste this link into your browser:
                                        <br/>
                                        <span style="color:#111827;word-break:break-all;">{{appealUrl}}</span>
                                        <br/><br/>
                                        Need help? Contact <a href="mailto:{{supportEmail}}"
                                                              style="color:#4F8A92;text-decoration:none;font-weight:700;">{{supportEmail}}</a>.
                                    </div>
                                </td>
                            </tr>--}}
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding:16px 6px 0 6px;">
                        <div
                            style="text-align:center;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#9CA3AF;">
                            {{date('Y')}} TBM Events.
                            <br/>
                            <span style="color:#9CA3AF;">Unforgettable experiences await you.</span>
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

