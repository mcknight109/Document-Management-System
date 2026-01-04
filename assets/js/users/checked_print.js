function numberToWords(num) {
    const ones = ["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine"];
    const teens = ["Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"];
    const tens = ["","","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"];

    function convert(n) {
        if (n < 10) return ones[n];
        if (n < 20) return teens[n - 10];
        if (n < 100) return tens[Math.floor(n / 10)] + (n % 10 ? " " + ones[n % 10] : "");
        if (n < 1000) return ones[Math.floor(n / 100)] + " Hundred " + convert(n % 100);
        if (n < 1000000) return convert(Math.floor(n / 1000)) + " Thousand " + convert(n % 1000);
        return "";
    }

    return convert(num).trim() + " Pesos";
}

function printCheck() {

    const selected = document.querySelector(".record-checkbox:checked");
    if (!selected) {
        Swal.fire("No Selection", "Please select one record to print.", "warning");
        return;
    }

    const docId = selected.value;

    fetch(`Controllers/CheckController.php?action=get_check_print&id=${docId}`)
        .then(res => res.json())
        .then(data => {

            const win = window.open('', '_blank', 'width=900,height=600');
            if (!win) {
                Swal.fire("Popup Blocked", "Please allow popups.", "warning");
                return;
            }

            const checkNoDigits = (data.check_no || "00000000")
                .padStart(8, "0")
                .split("");

            const date = data.check_date ? new Date(data.check_date) : null;
            const dd = date ? String(date.getDate()).padStart(2, "0") : "";
            const mm = date ? String(date.getMonth() + 1).padStart(2, "0") : "";
            const yy = date ? String(date.getFullYear()).slice(-2) : "";

            const amount = Number(data.amount || 0);
            const amountWords = numberToWords(Math.floor(amount));

            const html = `
<!DOCTYPE html>
<html>
<head>
<title>Check Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7c9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px 40px;
            box-sizing: border-box;
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 10px;
            margin-bottom: 15px;
        }
        .label-box {
            font-weight: normal;
        }
        .controlBox {
            display: flex;
            align-items: center;
            text-align: center;
            flex-direction: column;
        }
        .check-number {
            display: flex;
            gap: 3px;
            margin-top: 3px;
        }
        .check-box {
            border: 1px solid black;
            width: 22px;
            height: 22px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            line-height: 22px;
            background: white;
        }

        .date-section {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-size: 11px;
            gap: 4px;
        }
        
        .date-label {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 62px;
            padding-right: 25px;
            font-size: 7px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .date-part {
            display: flex;
            flex-direction: column;
            align-items: center;
            line-height: 1;     
        }

        .date-value {
            font-size: 15px;
            font-weight: bold;
            height: 14px;
        }

        .payee-amount-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .payee-section {
            flex: 1;
            font-size: 12px;
        }
        .payee-line {
            border-bottom: 1px solid black;
            display: inline-block;
            width: 590px;
            font-size: 15px;
            font-weight: bold;
            padding-left: 10px;
        }
        .amount-section {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .p-symbol {
            font-size: 1px;
            font-weight: bold;
        }
        .amount-box {
            border: 1px solid black;
            width: 200px;
            height: 22px;
            background: white;
            text-align: center;
        }
        .amount-row {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        .amount-line {
            border-bottom: 1px solid black;
            display: inline-block;
            width: 830px;
            font-size: 15px;
            font-weight: bold;
            padding-left: 10px;
        }

        .bottom-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .logo-area {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }
        .logo-area img {
            width: 80px;
            height: 80px;
        }
        .info-area {
            display: flex;
            font-size: 12px;
            line-height: 1.4;
            height: 100%;
            justify-content: center;
            align-items: flex-start;
            flex-direction: column;
        }
        .info-title {
            font-weight: bold;
            font-size: 13px;
        }
        .stamp-boxes {
            display: flex;
            gap: 15px;
        }
        .stamp-box {
            border: 1px solid black;
            width: 230px;
            height: 75px;
            background: white;
        }
    </style>
</head>

<body onload="window.print(); setTimeout(()=>window.close(),300);">

<div class="container">

    <div class="top-row">
        <div class="label-box">ACCOUNT NO.</div>
        <div class="label-box">ACCOUNT NAME.</div>

        <div class="controlBox">
            <div class="label-box">CHECK NO.</div>
            <div class="check-number">
                ${checkNoDigits.map(d => `<div class="check-box">${d}</div>`).join("")}
            </div>
        </div>

        <div class="label-box">BSTN</div>
    </div>

    <div class="date-section">
        <span>DATE:</span>

        <div class="date-part">
            <span class="date-value">${dd}</span>
            <span>__________</span>
        </div>

        <span>/</span>

        <div class="date-part">
            <span class="date-value">${mm}</span>
            <span>__________</span>
        </div>

        <span>/</span>

        <div class="date-part">
            <span class="date-value">${yy}</span>
            <span>__________</span>
        </div>
    </div>

    <div class="date-label">
        <span>DD</span>
        <span>MM</span>
        <span>YY</span>
    </div>

    <div class="payee-amount-row">
        <div class="payee-section">
            <strong>NAME OF <br>THE PAYEE:</strong>
            <span class="payee-line">${data.payee || ""}</span>
        </div>

        <div class="amount-section">
            <span class="p-symbol">₱</span>
            <div class="amount-box">${amount.toFixed(2)}</div>
        </div>
    </div>

    <div class="amount-row">
        <strong>PESOS:</strong>
        <span class="amount-line">${amountWords}</span>
    </div>

    <div class="bottom-section">
        <div class="logo-area">
            <img src="../assets/images/office-of-treasurer.png">
            <div class="info-area">
                <div class="info-title">CITY TREASURER'S OFFICE</div>
                <div><strong>ADMIN DIVISION</strong></div>
                <div>Mandaue City Garden</div>
                <div>8WHV+25Q, Mandaue, 6014 Cebu</div>
            </div>
        </div>

        <div class="stamp-boxes">
            <div class="stamp-box"></div>
            <div class="stamp-box"></div>
        </div>
    </div>

</div>
</body>
</html>
`;

            win.document.write(html);
            win.document.close();
        });
}
