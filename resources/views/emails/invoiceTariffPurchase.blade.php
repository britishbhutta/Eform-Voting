<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tariff Purchase Invoice</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 20px;
      color: #333;
    }
    .invoice-container {
      max-width: 700px;
      margin: auto;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 30px;
    }
    h2, h3 {
      margin-bottom: 10px;
      color: #222;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }
    table th, table td {
      text-align: left;
      padding: 10px;
      border-bottom: 1px solid #eee;
    }
    .total {
      font-weight: bold;
      font-size: 16px;
      color: #000;
    }
    .footer {
      margin-top: 20px;
      font-size: 12px;
      color: #777;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="invoice-container">
    <h2>Invoice</h2>
    <p><strong>Date:</strong> {{ date('Y-m-d') }}</p>

    <!-- User Details -->
    <h3>User Details</h3>
    <table>
      <tr>
        <th>First Name</th>
        <td>{{ $user->first_name }}</td>
      </tr>
      <tr>
        <th>Last Name</th>
        <td>{{ $user->last_name }}</td>
      </tr>
      <tr>
        <th>Email</th>
        <td>{{ $user->email }}</td>
      </tr>
    </table>

    <!-- Tariff Details -->
    <h3>Tariff Details</h3>
    <table>
      <tr>
        <th>Title</th>
        <td>{{ $tariff->title }}</td>
      </tr>
      <tr>
        <th>Description</th>
        <td>{{ $tariff->description }}</td>
      </tr>
      <tr>
        <th>Note</th>
        <td>{{ $tariff->note }}</td>
      </tr>
      <tr>
        <th>Features</th>
        <td>
          @if($tariff->features)
            <ul>
              @foreach(json_decode($tariff->features) as $feature)
                <li>{{ $feature }}</li>
              @endforeach
            </ul>
          @endif
        </td>
      </tr>
      <tr>
        <th>Price</th>
        <td>{{ number_format($tariff->price_cents / 100, 2) }} {{ $tariff->currency }}</td>
      </tr>
    </table>

    <!-- Booking / Billing Details -->
    <h3>Billing Details</h3>
    <table>
      <tr>
        <th>Company</th>
        <td>{{ $booking->company }}</td>
      </tr>
      <tr>
        <th>Company ID</th>
        <td>{{ $booking->company_id }}</td>
      </tr>
      <tr>
        <th>VAT No.</th>
        <td>{{ $booking->tax_vat_no }}</td>
      </tr>
      <tr>
        <th>Name</th>
        <td>{{ $booking->name }}</td>
      </tr>
      <tr>
        <th>Address</th>
        <td>{{ $booking->address }}</td>
      </tr>
      <tr>
        <th>City</th>
        <td>{{ $booking->city }}</td>
      </tr>
      <tr>
        <th>Zip</th>
        <td>{{ $booking->zip }}</td>
      </tr>
      <tr>
        <th>Country</th>
        <td>{{ $booking->country }}</td>
      </tr>
      <tr>
        <th>Phone</th>
        <td>{{ $booking->phone }}</td>
      </tr>
      <tr>
        <th>Email</th>
        <td>{{ $booking->email }}</td>
      </tr>
    </table>

    <p class="total">Total: {{ number_format($tariff->price_cents / 100, 2) }} {{ $tariff->currency }}</p>

    <div class="footer">
      <p>Thank you for your purchase!</p>
      <p>&copy; {{ date('Y') }} Your Company Name</p>
    </div>
  </div>
</body>
</html>
