function printSlip() {
    const rows = document.querySelectorAll('.rowCheckbox');
    const checkedRows = Array.from(rows).filter(cb => cb.checked);

    if (checkedRows.length > 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Action',
            text: 'Please select only one record to print.',
        });
        return;
    }

    const com_id = document.getElementById("com_id").value;
    const date_received = document.getElementById("date_received").value;
    const sender = document.getElementById("sender").value;
    const description = document.getElementById("description").value;

    if (!com_id || !date_received || !sender || !description) {
        Swal.fire("Missing Information", "Please select or fill out an IN Form first.", "warning");
        return;
    }

    const formattedDate = new Date(date_received).toLocaleDateString("en-US", {
        month: "2-digit",
        day: "2-digit",
        year: "numeric"
    });

    const printContent = `
    <html>
    <head>
        <title>Routing Slip</title>
        <style>
            @page { size: A4; margin: 10mm; }
            body { font-family: Arial, sans-serif; font-size: 12px; }

            .page {
                width: 50%;
                border: 1px solid black;
                padding: 10px;
                box-sizing: border-box;
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid black;
                padding-bottom: 10px;
            }

            .header img {
                width: 50px;
                height: 50px;
            }

            .center-title {
                text-align: center;
                font-weight: bold;
                font-size: 16px;
                margin-top: 5px;
            }

            .subject-box {
                width: 100%;
                text-align: center;
                font-size: 10px;
                padding-bottom: 10px;
                border-bottom: 1px solid black;
            }

            .subject-box p{
                padding: 0;
                margin: 0;
            }

            .to-section {
                margin-top: 10px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                padding: 10px 0px;
                gap: 10px;
                border-bottom: 1px solid black;
                font-size: 10px;
            }

            .remarks-box {
                margin-top: 15px;
                height: 120px;
                font-size: 12x;
                text-align: left;
            }

            .sign {
                margin-top: 20px;
                text-align: right;
                font-weight: bold;
            }

            .footer {
                margin-top: 20px;
                text-align: left;
                font-size: 10px;
            }
        </style>
    </head>
    <body>
        <div class="page">

            <!-- HEADER -->
            <div class="header">
                <img src="/document-recordsys/assets/images/office-of-treasurer.png">
                <div style="text-align:center;">
                    <div>Office of the Treasurer</div>
                    <div><strong>INTER-OFFICE <br>
                    ROUTING SLIP</strong></div>
                </div>
                <img src="/document-recordsys/assets/images/office-of-treasurer.png">
            </div>

            <div class="subject-box">
                <p style="text-align: right;">${formattedDate}</p><br>
                <p style="text-align: left; font-style: italic; font-weight: bold;">Sender:</p><br>
                <span style="font-weight: bold;">${sender}</span><br><br>
                <p>${description.toUpperCase()}</p>
            </div>

            <div class="to-section">
                <div>
                    <strong style="font-style: italic;">INDORSE TO:</strong><br>
                    ⬜ADMIN DIVISION<br>
                    ⬜LANDTAX DIVISION<br>
                    ⬜LICENSE DIVISION<br>
                    ⬜CASH DIVISION<br>
                    ⬜TORU DIVISION<br>
                    ⬜RECORDS SECTION<br>
                    ⬜BUS. TAX MAPPING SECTION<br>
                    ⬜GRACE ATTY. TERENCE<br>
                </div>

                <div>
                    <strong style="font-style: italic;">ACTION:</strong><br>
                    ⬜U-R-G-E-N-T<br>
                    ⬜APPROPRIATE ACTION<br>
                    ⬜COMMENT/SAND/OR<br>
                    ⬜REPRESENT THIS OFFICE<br>
                    ⬜TAKE UP WITH ME<br>
                    ⬜FURNISH COPY<br>
                    ⬜FILE<br>
                </div>
            </div>

            <div class="remarks-box">
                <span style="font-style: italic;">REMARKS:</span><br><br>
                _________________________________________________<br>
                _________________________________________________<br>
                _________________________________________________<br>
                _________________________________________________<br>
                _________________________________________________
            </div>

            <div class="sign">
                <br>
                CLAIRE V. GABALDA<br>
                <span style="font-size: 10px;">Acting City Treasurer</span>
            </div>

            <div class="footer">2025</div>
        </div>
    </body>
    </html>
    `;

    const win = window.open("", "_blank", "width=900,height=600");
    win.document.write(printContent);
    win.document.close();
    win.print();
}
