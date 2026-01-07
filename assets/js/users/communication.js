function printSlip() {
    const rows = document.querySelectorAll(".rowCheckbox");
    const checkedRows = Array.from(rows).filter((cb) => cb.checked);

    if (checkedRows.length > 1) {
        Swal.fire({
            icon: "warning",
            title: "Invalid Action",
            text: "Please select only one record to print.",
        });
        return;
    }

    const com_id = document.getElementById("com_id").value;
    const date_received = document.getElementById("date_received").value;
    const sender = document.getElementById("sender").value;
    const description = document.getElementById("description").value;
    const indorse_to = document.getElementById("indorse_to").value;
    const action = document.getElementById("action").value;
    const remarks = document.getElementById("remarks").value;

    if (!com_id || !date_received || !sender || !description) {
        Swal.fire(
            "Missing Information",
            "Please select or fill out an IN Form first.",
            "warning"
        );
        return;
    }

    const formattedDate = new Date(date_received).toLocaleDateString("en-US", {
        month: "2-digit",
        day: "2-digit",
        year: "numeric",
    });

    // Define all possible indorse options
    const indorseOptions = [
        "ADMIN DIVISION",
        "LANDTAX DIVISION",
        "LICENSE DIVISION",
        "CASH DIVISION",
        "TORU DIVISION",
        "RECORDS SECTION",
        "BUS. TAX MAPPING SECTION",
        "GRACE ATTY. TERENCE",
    ];

    // Define all possible action options
    const actionOptions = [
        "U-R-G-E-N-T",
        "APPROPRIATE ACTION",
        "COMMENT/S AND/OR",
        "REPRESENT THIS OFFICE",
        "TAKE UP WITH ME",
        "FURNISH COPY",
        "FILE",
    ];

    // Generate indorse section with checkboxes
    let indorseSectionHTML = "";
    indorseOptions.forEach((option) => {
        // Check if this option matches the indorse_to value (case-insensitive)
        const isChecked =
            indorse_to &&
            option.toLowerCase().includes(indorse_to.toLowerCase())
                ? "✓"
                : "□";
        indorseSectionHTML += `${isChecked} ${option}<br>`;
    });

    // Generate action section with checkboxes
    let actionSectionHTML = "";
    actionOptions.forEach((option) => {
        // Check if this option matches the action value (case-insensitive)
        const isChecked =
            action && option.toLowerCase().includes(action.toLowerCase())
                ? "✓"
                : "□";
        actionSectionHTML += `${isChecked} ${option}<br>`;
    });

    // Format remarks - put text above the lines
    let remarksHTML = "";
    if (remarks && remarks.trim() !== "") {
        // Split remarks by lines and create underlined format
        const remarksLines = remarks.split("\n");
        remarksLines.forEach((line) => {
            if (line.trim() !== "") {
                remarksHTML += `<div style="margin-bottom: 5px;">${line}</div>`;
            }
        });
    }

    const printContent = `
    <html>
    <head>
        <title>Routing Slip</title>
        <style>
            @page { size: A4; margin: 10mm; }
            body { font-family: Arial, sans-serif; font-size: 12px; }

            .page {
                width: 45%;
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
                font-size: 12px;
                text-align: left;
                min-height: 120px;
                padding: 5px;
            }

            .remarks-text {
                margin-top: 20px;
                line-height: 1.4;
                text-align: center;
            }

            .underline-line {
                border-bottom: 1px solid black;
                margin-bottom: 5px;
                height: 18px;
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
            
            .checkbox-item {
                font-size: 8px;
                line-height: 1.4;
            }
            
            .checked-box {
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="page">

            <!-- HEADER -->
            <div class="header">
                <img src="../${siteLogo}" alt="Website Logo" class="logo">
                <div style="text-align:center;">
                    <div>Office of the Treasurer</div>
                    <div><strong>INTER-OFFICE <br>ROUTING SLIP</strong></div>
                </div>
                <img src="../${siteLogo}" alt="Website Logo" class="logo">
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
                    <div class="checkbox-item">
                        ${indorseSectionHTML}
                    </div>
                </div>

                <div>
                    <strong style="font-style: italic;">ACTION:</strong><br>
                    <div class="checkbox-item">
                        ${actionSectionHTML}
                    </div>
                </div>
            </div>

            <div class="remarks-box">
                <span style="font-style: italic;">REMARKS:</span><br>
                <div class="remarks-text">
                    ${remarksHTML}
                </div>
            </div>

            <div class="sign">
                <br>
                CLAIRE V. GABALDA<br>
                <span style="font-size: 10px;">Acting City Treasurer</span>
            </div>

            <div class="footer">${com_id}</div>
        </div>
    </body>
    </html>
    `;

    const win = window.open("", "_blank", "width=900,height=600");
    win.document.write(printContent);
    win.document.close();
    win.print();
}
