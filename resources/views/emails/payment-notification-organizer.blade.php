<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>New ticket purchase</title>
</head>
<body style="margin:0;padding:0;background-color:#F8FAFC;">
<div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
    New ticket purchase for {{ $event->title }}.
</div>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#F8FAFC;">
    <tr>
        <td align="center" style="padding:32px 12px;">
            <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:600px;max-width:600px;">
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
                                                    Organizer Payment Alert
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="right" style="padding:0 4px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:16px;color:#9CA3AF;">
                                        {{ \Carbon\Carbon::parse($transaction->updated_at)->format('d/m/y H:i:s') }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="background-color:#FFFFFF;border:1px solid rgba(15,23,42,0.08);border-radius:4px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,0.06);">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:18px 22px;background:#E8B025;">
                                    <div style="font-family:Manrope,Inter,Segoe UI,Arial,sans-serif;font-size:18px;line-height:24px;font-weight:800;color:#0F172A;">
                                        New ticket purchase
                                    </div>
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;color:rgba(15,23,42,0.9);margin-top:4px;">
                                        {{ '₦' .  $transaction->amount }} — {{ $ticket->name }} Ticket {{ $transaction->coupon ? '(with coupon)' : '' }}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <td style="padding:16px 22px 18px 22px;">
                                    <div style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:20px;color:#4B5563;">
                                        Event: <span style="color:#111827;font-weight:700;">{{ $event->title }}</span><br />
                                        Customer: <span style="color:#111827;">{{ $customer->full_name }}</span><br />
                                        Purchased: <span style="color:#111827;">{{ \Carbon\Carbon::parse($transaction->updated_at)->format('d/m/y H:i:s') }}</span>
                                    </div>

                                    {{--<div style="margin-top:14px;">
                                        <a href="{{organizerSalesUrl}}" style="font-family:Inter,Segoe UI,Arial,sans-serif;font-size:13px;line-height:18px;font-weight:800;color:#4F8A92;text-decoration:none;">View sales →</a>
                                    </div>--}}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 6px 0 6px;">
                        <div style="text-align:center;font-family:Inter,Segoe UI,Arial,sans-serif;font-size:12px;line-height:18px;color:#9CA3AF;">
                            Automated notification from TBM Events.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
