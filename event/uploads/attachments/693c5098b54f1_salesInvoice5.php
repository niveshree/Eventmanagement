<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;

            font-size: 14px;
            line-height: 18px;
            color: #555;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        body {
            font-family: 'Heebo', sans-serif;
        }

        .top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .top img {
            width: 150px;
        }

        .invoice {
            display: flex;
            align-items: center;
            width: 210px;
            font-size: 15px;
            justify-content: space-between;
            font-weight: 500;
            height: 25px;
        }

        .text-center {
            text-align: center;
        }

        .costomer {
            background: rgb(54, 54, 54);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 50px;
            padding: 0 20px;
        }

        .costomer p:last-child {
            margin-right: 250px;
        }

        .table-one {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 0 20px 20px 20px;
            border: 1px solid #a9a9a9;
            margin-top: -10px;
        }

        .table-one .avery {
            width: 28%;
            margin-right: 100px;
        }

        .example {
            width: 30%;
        }

        .example h3 {
            font-size: 17px;
            height: 4px;
        }

        .avery h3 {
            width: 87%;
            margin-bottom: -13px;
        }

        .table-one p {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 0;
        }

        .table-one span {
            display: block;
            font-weight: 500;
        }

        .invoice-box table tr.item td {
            border-right: 1px solid black;
            border-bottom: 1px solid black;
            border-left: 1px solid black;
            padding: 10px;
        }

        .invoice-box table tr.item.item-two td {
            border-right: 1px solid black;
            border-left: 1px solid black;
            border-bottom: none;
        }

        .invoice-box table tr.item.item-three td {
            border-top: 1px solid #a9a9a9;
        }

        .table-two {
            background: #fff;
            margin-top: 40px;
        }

        .table-two table thead {
            background: rgb(54, 54, 54);
            color: #fff;
        }

        .table-two thead th {
            padding: 6px;
        }

        .table-two table {
            width: 100%;
        }

        /* tr.item.heading-tow {
            background: #f8f8f8;
        } */

        /* tr.item.heading-tow {
            background: #f8f8f8;
        } */




        .invoice-box table tr.heading td {
            background: #363636;
            color: #fff;
            padding: 6px;
        }

        .status {
            border: 1px solid #a9a9a9;
            margin-top: 40px;
            display: flex;
            align-items: center;
        }

        .table-three {
            border-left: 1px solid #a9a9a9;
            width: 100%;
        }

        .invoice-box table tr.item-four td {
            border-right: 1px solid #a9a9a9;
            border-bottom: 1px solid #a9a9a9;
            text-align: end;
            padding: 5px;
        }

        .invoice-box table tr.item-four td:last-child {
            border-right: 1px solid #a9a9a9;
            border-bottom: 1px solid #a9a9a9;
            text-align: start;
            padding: 5px;
        }

        .invoice-box table tr.item-four th {
            border-right: 1px solid #a9a9a9;
            border-bottom: 1px solid #a9a9a9;
            text-align: end;
            padding: 5px;
        }

        .status-due span {
            display: block;
            font-size: 15px;
        }

        .status-due {
            padding-left: 10px;
        }

        span.paid {
            margin-top: 15px;
        }

        .status-due h4 {
            color: #000;
            font-size: 18px;
            margin: 0 0 15px 0;
        }

        table {
            width: 400px;
        }

        .signertuer span {
            display: block;
            font-size: 16px;
            font-weight: 500;
        }

        .signertuer img {
            width: 100px;
            margin: 10px 0;
        }

        .signertuer {
            display: flex;
            align-items: center;
            justify-content: end;
            margin-right: 70px;
        }

        .divider {
            line-height: 1.5715;
            color: #000000d9;
            border-top: 1px solid rgba(0, 0, 0, .7);
            margin: 30px 0;
        }

        .trem span {
            font-size: 13px;
            font-weight: 700;
            color: #000;
        }

        .trem p {
            color: #000;
        }

        table.information {
            width: 100%;
        }


        .text-right {
            text-align: right;
        }

        .p-5 {
            padding-bottom: 5px;
        }

        .space-10 {
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .space-3 {
            padding-bottom: 3px;
            margin-bottom: 3px;
        }

        .pl-15 {
            padding-left: 15px;
        }

        .pt-15 {
            padding-top: 15px;
        }

        .pb-15 {
            padding-bottom: 15px;
        }

        .mt-20 {
            margin-top: 10px;
        }

        .clearfix {
            display: block;
            clear: both;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <div style="width:100%;">
            <table style="width: 100%;">
                <tr style="vertical-align: top; width:40%">
                    <td style="text-align:left;width:20%;vertical-align:top;padding:0px">
                        <img src="{{ asset('images/flipkart.jpeg') }}" style="width: 100px;height:90px;color:blue" />
                    </td>

                    <td style="font-weight:12px;width:30%;margin-top:-10px">
                        <span style="font-size: 16px; font-weight: bold">{{ $company->name }}</span> <br>
                        <span style="font-size:12px">{{ $company->address }}</span><br>
                        <span style="font-size:12px">{{ $company->address }}</span><br>
                        <span style="font-size:12px">{{ $company->email }}</span><br>
                        <span style="font-size:12px">{{ $company->phone }}</span>
                    </td>
                    <td style="width: 60%;">
                        <table>
                            <tr>
                                <td style="text-align: right;">
                                    <span style="font-size: 15px; font-weight: bold;padding:2px">
                                        @if($order->order_type == "purchases")
                                        {{ $traslations['purchase_invoice'] }}
                                        @elseif($order->order_type == "purchase-returns")
                                        {{ $traslations['purchase_return_invoice'] }}
                                        @elseif($order->order_type == "sales-returns")
                                        {{ $traslations['sales_return_invoice'] }}
                                        @elseif($order->order_type == "sales")
                                        {{ $traslations['sales_invoice'] }}
                                        @elseif($order->order_type == "quotations")
                                        {{ $traslations['quotation_invoice'] }}
                                        @endif
                                    </span><br>
                                    <span style="font-size: 10px;">
                                        ORIGINAL FOR RECIPIENT
                                    </span><br>
                                    <span style="text-align:right;font-size:11px"><span>{{ 'Invoice #:' }}</span><span>{{ 'INV-01' }}</span></span><br>
                                    <span style="text-align:right;font-size:11px"><span>{{ 'Due Date:' }}</span><span>{{ '21-Dec-24' }}</span></span><br>
                                    <span style="text-align:right;font-size:11px"><span>{{ 'Place of Supply:' }}</span><span>{{'07-Delhi'}}</span></span>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div style="width: 100%;">
            <div style="width:50%;float:left">
                <table>
                    <tr>
                        <td style="text-align: left;font-size: 13px;font-weight:bolder">{{'Bill From:'}}</td>
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td>
                            <span style="font-size: 12px; font-weight: bold">{{ $company->name }}</span> <br>
                            <span style="font-size:12px">{{ $company->address }}</span><br>
                            <span style="font-size:12px">{{ $company->phone }}</span><br>
                            <span style="font-size:12px">{{ $company->email }}</span><br>
                        </td>
                        <td>&nbsp;</td>
                    </tr>
                </table>
            </div>
        </div>
        <div style="width:50%;float:right">
            <table>
                <tr>
                    <td style="text-align: left;font-size: 13px;font-weight:bolder">{{'Bill To:'}}</td>
                </tr>
                <tr>
                    <span style="font-size: 12px; font-weight: bold">{{ $company->name }}</span> <br>
                    <span style="font-size:12px">{{ $company->address }}</span><br>
                    <span style="font-size:12px">{{ $company->phone }}</span><br>
                    <span style="font-size:12px">{{ $company->email }}</span><br>
                </tr>
            </table>
        </div>
    </div>
    <div style="width: 100%; margin-top:110px">
        <div style="width:50%;float:left">
            <table>
                <tr>
                    <td style="text-align: left;font-size: 13px;font-weight:bolder">{{'Ship From:'}}</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>
                        <span style="font-size: 12px; font-weight: bold">{{ $company->name }}</span> <br>
                        <span style="font-size:12px">{{ $company->address }}</span><br>
                        <span style="font-size:12px">{{ $company->phone }}</span><br>
                        <span style="font-size:12px">{{ $company->email }}</span><br>
                    </td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>
    <div style="width:50%;float:right">
        <table>
            <tr>
                <td style="text-align: left;font-size: 13px;font-weight:bolder">{{'Ship To:'}}</td>
            </tr>
            <tr>
                <span style="font-size: 12px; font-weight: bold">{{ $company->name }}</span> <br>
                <span style="font-size:12px">{{ $company->address }}</span><br>
                <span style="font-size:12px">{{ $company->phone }}</span><br>
                <span style="font-size:12px">{{ $company->email }}</span><br>
            </tr>
        </table>
    </div>
    </div>
    <div style="margin-top: 140px;">
        @php($count=0)
        <table cellpadding="2px" cellspacing="0" style="font-size:12px; vertical-align: top;width:100%;border:1px solid black">
            <tbody>
                <tr class="item heading-tow" style="border:1px solid black">
                    <td style="text-align: left;">#</td>
                    <td style="text-align: left;">{{ 'Item' }}</td>
                    <td style="text-align: center;">{{ 'HSN/SAC' }}</td>
                    <td style="text-align: center;">{{ 'Rate/Item' }}</td>
                    <td style="text-align: center;">{{'Qty'}}</td>
                    <td style="text-align: center;">{{'Taxable Amount'}}</td>
                    <td style="text-align: center;">{{'Taxable Value'}}</td>
                    <td style="text-align: center;">{{ 'Amount' }}</td>
                </tr>
                @foreach($order->items as $item)
                @php($count += $item->quantity)
                <tr class="item heading-tow" style="vertical-align: top;height:7%">
                    <td style="border-left:none;border-bottom:1px solid gray">{{ $loop->iteration }}</td>
                    <td style="font-size: 14px; font-weight: bold;border-bottom:1px solid gray">{{ $item->product->name }}</td>
                    <td style="text-align: center;border-bottom:1px solid gray">{{ '1005'}}</td>
                    <td style="text-align: center;border-bottom:1px solid gray">{{ App\Classes\Common::formatAmountCurrency($company->currency, $item->single_unit_price) }}</td>
                    <td style="text-align: center;border-bottom:1px solid gray">{{ $item->quantity . ' ' . $item->unit->short_name }}</td>
                    <td style="text-align: right;border-bottom:1px solid gray">{{ App\Classes\Common::formatAmountCurrency($company->currency, $item->subtotal) }}</td>
                    <td style="text-align: right;border-bottom:1px solid gray">{{ App\Classes\Common::formatAmountCurrency($company->currency, $item->subtotal) }}</td>
                    <td style="text-align: right;border-bottom:1px solid gray">{{ App\Classes\Common::formatAmountCurrency($company->currency, $item->subtotal) }}</td>
                </tr>
                @endforeach

                <tr>
                    <td style="font-size:10px;text-align: left;border-bottom:2px solid blue">{{'Total Items/Qty:2/2.000'}}</td>
                    <td style="text-align: center;border-bottom:2px solid blue">&nbsp;</td>
                    <td style="text-align: center;">&nbsp;</td>
                    <td style="text-align: center;">&nbsp;</td>
                    <td style="text-align: right;border-bottom:2px solid blue;font-size:10px">{{'Total Amount(in words):'}}</td>
                    <td colspan="3" style="text-align: right;border-bottom:2px solid blue;font-size:10px">{{'Nine Thousands Seven Hundred Ninty Two Only'}}</td>
                    <td style="text-align: center;border-bottom:2px solid blue"></td>
                    <td style="text-align: right;border-bottom:2px solid blue"></td>
                </tr>
        </table>
    </div>
    <div style="width: 100%;display:flex">

        <div style="width:25%;margin-top:20px">
            <table style="width: 100%">
                <tr>
                    <td style="text-align:left;width:40%;font-weight:bold;font-size:12px">{{'Pay Using UPI:'}}</td>
                </tr>
                <tr>
                    <td style="text-align:left;width:20%;vertical-align:top">
                        <img src="{{ asset('images/qrcode.png') }}" style="width: 90px;height:80px; margin-top: 5px;" />
                        <!-- <img src="'data:image/png;base64,{{'$barcodeData'}}'" alt="barcode" style="width:100px;height:25px"  /> -->
                    </td>
                </tr>
            </table>
        </div>
        <div style="width:75%;float:right;margin-top:-113px">
            <table style="width: 100%">
                <tr>
                    <td style="text-align: left;width:30%;font-weight:bold">{{'Bank Details:'}}</td>
                    <td style="text-align: left;width:50%"></td>
                    <td style="text-align: left;width:10%"></td>
                </tr>
                <tr>
                    <td style="text-align: left;width:30%;font-size:11px">{{'Account #:'}}</td>
                    <td style="text-align: left;width:50%;font-size:11px">{{'667342423462345'}}</td>
                    <td style="text-align: left;width:10%"></td>
                </tr>
                <tr>
                    <td style="text-align: left;width:30%;font-size:11px">{{'IFSC:'}}</td>
                    <td style="text-align: left;width:50%;font-size:11px">{{'YESBBIN0654'}}</td>
                    <td style="text-align: left;width:10%"></td>
                </tr>
                <tr>
                    <td style="text-align: left;width:30%;font-size:11px">{{'Branch:'}}</td>
                    <td style="text-align: left;width:50%;font-size:11px">{{'Kodihilie'}}</td>
                    <td style="text-align: left;width:10%"></td>
                </tr>
            </table>
        </div>
        <div style="width:30%;float:right;margin-top:-89px">
            <table>
                <tr>
                    <td style="text-align: right;"><span>{{'Taxable Amount'}}</span></td>
                    <td style="text-align: center;">&nbsp;</td>
                    <td style="text-align: right;">{{'$8298.00'}}</td>
                </tr>
                <tr>
                    <td style="text-align: right;">{{'IGST'}}</td>
                    <td style="text-align: center;">&nbsp;</td>
                    <td style="text-align: right;">{{'$1493.64'}}</td>

                </tr>
                <tr>
                    <td style="text-align: right;border-top:1px solid gray">{{'Total'}}</td>
                    <td style="text-align: center;border-top:1px solid gray">&nbsp;</td>
                    <td style="text-align: right;border-top:1px solid gray">{{'$9792.00'}}</td>
                </tr>
            </table>
        </div>
    </div>
    <div style="margin-top: 120px;">
        <table style="width: 100%">
            <tr>
                <td style="text-align: left;width:100%;font-weight:bold">{{'Notes:'}}</td>
            </tr>
            <tr>
                <td style="text-align: left;width:100%;font-size:11px">{{'Thank you for the Business.'}}</td>
            </tr>
        </table>
    </div>
    <div style="margin-top: 10px;">
        <table style="width: 100%">
            <tr>
                <td style="text-align: left;width:100%;font-weight:bold">{{'Terms and Conditions:'}}</td>
            </tr>
            <tr>
                <td style="text-align: left;width:100%;font-size:11px">{!! nl2br($warehouse->terms_condition) !!}</td>
            </tr>
        </table>
    </div>
    </div>
</body>

</html>