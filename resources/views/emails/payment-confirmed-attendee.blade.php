<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>Payment confirmed</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;">
<!-- Preheader -->
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    Payment confirmed — your ticket for {{ $ticketDetails['event_title'] }} is ready.
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
                                                    Receipt & Ticket
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="right" style="padding:0 4px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#9CA3AF;">
                                        {{ $ticketDetails['date_time_purchased'] }}
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
                                        Payment confirmed
                                    </div>
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(15,23,42,0.9);margin-top:4px;">
                                        Your ticket is ready for {{ $ticketDetails['event_title'] }}.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:18px 22px 0 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:14px;line-height:22px;color:#111827;">
                                        Hi {{ $attendee->full_name }},
                                        <br /><br />
                                        We’ve received your payment via <strong>{{ $ticketDetails['gateway'] }}</strong>. Keep this email — you may need it for entry.
                                    </div>
                                </td>
                            </tr>

                            <!-- Ticket summary -->
                            <tr>
                                <td style="padding:14px 22px 16px 22px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F9FAFB;border:1px solid rgba(15,23,42,0.06);border-radius:14px;">
                                        <tr>
                                            <td style="padding:14px 14px 8px 14px;">
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#0F172A;">
                                                    Ticket details
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:0 14px 14px 14px;">
                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:20px;color:#4B5563;">
                                                    Ticket ID: <span style="color:#111827;font-weight:700;">{{ $ticketDetails['id'] }}</span><br />
                                                    Ticket name: <span style="color:#111827;">{{ $ticketDetails['ticket_name'] }}</span><br />
                                                    Purchased: <span style="color:#111827;">{{ $ticketDetails['date_time_purchased'] }}</span><br />
                                                    Payment gateway: <span style="color:#111827;">{{ $ticketDetails['gateway'] }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- Event details -->
                            <tr>
                                <td style="padding:0 22px 16px 22px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid rgba(15,23,42,0.08);border-radius:14px;">
                                        <tr>
                                            <td style="padding:14px 14px 8px 14px;">
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#0F172A;">
                                                    Event details
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:0 14px 14px 14px;">
                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:20px;color:#4B5563;">
                                                    Event: <span style="color:#111827;">{{ $ticketDetails['event_title'] }}</span><br />
                                                    Location: <span style="color:#111827;">{{ $ticketDetails['event_location'] }}</span><br />
                                                    Virtual link: <a href="{{ $ticketDetails['event_link'] }}" style="color:#4F8A92;text-decoration:none;font-weight:700;">{{ $ticketDetails['event_link'] }}</a>
                                                </div>
                                                <div style="margin-top:10px;">
                                                    <a href="{{ config('app.client_url') . "/events/" . $ticketDetails['event_id'] }}" style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#4F8A92;text-decoration:none;font-weight:800;">View event page →</a>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <!-- QR code -->
                            <tr>
                                <td style="padding:0 22px 18px 22px;">
                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F9FAFB;border:1px dashed rgba(15,23,42,0.2);border-radius:16px;">
                                        <tr>
                                            <td style="padding:14px 14px 0 14px;">
                                                <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#0F172A;">
                                                    Entry QR code
                                                </div>
                                                <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;margin-top:4px;">
                                                    Show this at the entrance (or when requested by the host).
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="center" style="padding:14px;">
                                                <img
                                                    src="{{ $ticketDetails['qr_code'] }}"
                                                    width="220"
                                                    height="220"
                                                    alt="QR code for ticket {{ $ticketDetails['id'] }}"
                                                    style="display:block;border:0;outline:none;text-decoration:none;border-radius:16px;background-color:#FFFFFF;"
                                                />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:0 22px 22px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#6B7280;">
                                        Need help? Contact <a href="mailto:{{ config('mail.support_email') }}" style="color:#4F8A92;text-decoration:none;font-weight:800;">{{ config('mail.support_email') }}</a>.
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
                            © {{ date('Y') }} TBM Events. All rights reserved.
                            <br />
                            {{--<span style="color:#9CA3AF;">{{companyAddress}}</span>--}}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
