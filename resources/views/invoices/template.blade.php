<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 16px;
            color: #666;
        }
        .invoice-date {
            font-size: 14px;
            color: #666;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #007bff;
        }
        .info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-column {
            flex: 1;
        }
        .info-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-value {
            margin-bottom: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-row {
            margin-bottom: 10px;
        }
        .total-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .total-value {
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            border-top: 2px solid #007bff;
            padding-top: 10px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="invoice-title">E-Learning Platform</div>
        <div class="invoice-number">Invoice: {{ $invoice_number }}</div>
        <div class="invoice-date">Date: {{ $invoice_date }}</div>
    </div>

    <div class="section">
        <div class="section-title">Bill To</div>
        <div class="info-grid">
            <div class="info-column">
                <div class="info-label">Name:</div>
                <div class="info-value">{{ $user->name }}</div>
                
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $user->email }}</div>
                
                @if($user->phone)
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $user->phone }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Course Details</div>
        <div class="info-grid">
            <div class="info-column">
                <div class="info-label">Course Title:</div>
                <div class="info-value">{{ $course->title }}</div>
                
                <div class="info-label">Instructor:</div>
                <div class="info-value">{{ $course->instructor->name }}</div>
                
                <div class="info-label">Category:</div>
                <div class="info-value">{{ $course->category->name }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Payment Details</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Course Enrollment: {{ $course->title }}</td>
                    <td>${{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Subtotal:</span>
            <span class="total-value">${{ number_format($payment->amount, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Tax:</span>
            <span class="total-value">$0.00</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">Total:</span>
            <span class="total-value">${{ number_format($payment->amount, 2) }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Payment Information</div>
        <div class="info-grid">
            <div class="info-column">
                <div class="info-label">Transaction ID:</div>
                <div class="info-value">{{ $payment->transaction_id }}</div>
                
                <div class="info-label">Payment Method:</div>
                <div class="info-value">{{ ucfirst($payment->payment_method) }}</div>
                
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($payment->status) }}</div>
                
                <div class="info-label">Payment Date:</div>
                <div class="info-value">{{ $payment->created_at->format('F d, Y g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for your purchase!</p>
        <p>This is a computer-generated invoice. No signature required.</p>
        <p>For support, please contact us at support@elearning-platform.com</p>
    </div>
</body>
</html> 