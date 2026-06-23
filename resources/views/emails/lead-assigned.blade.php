<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isReassigned ? 'Lead reassigned' : 'New lead assigned' }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#10253f;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border:1px solid #dbe2ea;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 28px;background:#10253f;color:#ffffff;">
                            <p style="margin:0 0 6px;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;opacity:0.85;">
                                {{ config('app.name') }}
                            </p>
                            <h1 style="margin:0;font-size:22px;line-height:1.35;font-weight:700;">
                                {{ $isReassigned ? 'Lead reassigned to you' : 'New lead assigned to you' }}
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 28px;">
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                                Hello {{ $agent->name }},
                            </p>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;">
                                @if ($isReassigned)
                                    A lead has been reassigned to you. Review the details below and follow up with the customer.
                                @else
                                    A new lead has been assigned to you. Review the details below and follow up with the customer.
                                @endif
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;font-size:14px;">
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;width:38%;font-weight:600;">Lead ID</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">#{{ $lead->id }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Customer name</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">{{ $lead->customer_name ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Phone number</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">{{ $lead->phone_number ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Email</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">{{ $lead->email ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Company</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">{{ $lead->company?->name ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">City</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">{{ $lead->city ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Total passengers</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                                        {{ $lead->total_passengers !== null ? number_format((int) $lead->total_passengers) : '—' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Status</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">{{ $lead->statusLabel() }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;">Assigned on</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;">
                                        {{ $lead->lead_assign_date?->format('M j, Y g:i A') ?? ($lead->created_at?->format('M j, Y g:i A') ?? '—') }}
                                    </td>
                                </tr>
                                @if(!empty($lead->notes))
                                <tr>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;background:#f8fafc;font-weight:600;vertical-align:top;">Notes</td>
                                    <td style="padding:10px 12px;border:1px solid #e2e8f0;white-space:pre-wrap;">{{ $lead->notes ?: '—' }}</td>
                                </tr>
                                @endif
                            </table>

                            <p style="margin:24px 0 0;text-align:center;">
                                <a href="{{ $leadUrl }}"
                                   style="display:inline-block;padding:12px 20px;background:#10253f;color:#ffffff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600;">
                                    View lead in CRM
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
