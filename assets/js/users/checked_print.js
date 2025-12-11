function printCheck() {
    const win = window.open('', '_blank', 'width=900,height=600');

    if (!win) {
        Swal.fire("Popup Blocked", "Please allow popups for this site.", "warning");
        return;
    }

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
            }
            .amount-section {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .p-symbol {
                font-size: 18px;
                font-weight: bold;
            }
            .amount-box {
                border: 1px solid black;
                width: 200px;
                height: 22px;
                background: white;
            }
            .amount-row {
                font-size: 12px;
                font-weight: bold;
                margin-bottom: 30px;
            }
            .amount-line {
                border-bottom: 1px solid black;
                display: inline-block;
                width: 848px;
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
                        <div class="check-box"></div>
                        <div class="check-box"></div>
                        <div class="check-box"></div>
                        <div class="check-box"></div>
                        <div class="check-box"></div>
                        <div class="check-box"></div>
                        <div class="check-box"></div>
                        <div class="check-box"></div>
                    </div>
                </div>
                <div class="label-box">BSTN</div>
            </div>

            <div class="date-section">
                <span>DATE:</span>
                <span>__________</span>
                <span>/</span>
                <span>__________</span>
                <span>/</span>
                <span>__________</span>
            </div>
            <div class="date-label">
                <span>DD</span>
                <span>MM</span>
                <span>YY</span>
            </div>

            <div class="payee-amount-row">
                <div class="payee-section">
                    <strong>NAME OF <br>THE PAYEE:</strong> 
                    <span class="payee-line"></span>
                </div>
                <div class="amount-section">
                    <span class="p-symbol">₱</span>
                    <div class="amount-box"></div>
                </div>
            </div>

            <div class="amount-row">
                <strong>PESOS:</strong> <span class="amount-line"></span>
            </div>

            <div class="bottom-section">
                <div class="logo-area">
                    <img src="../assets/images/office-of-treasurer.png" alt="Logo">
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
}
