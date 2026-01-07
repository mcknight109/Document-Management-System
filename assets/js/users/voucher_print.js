async function printSelectedTransmittal() {
    const selected = [...document.querySelectorAll(".rowCheckbox:checked")];

    if (selected.length === 0) {
        Swal.fire("No record selected", "Select at least one row.", "info");
        return;
    }

    // Collect selected IDs
    const ids = selected.map((cb) => cb.value);

    // === STEP 3: Request new Transmittal ID + Update DB ===
    let result;
    try {
        const response = await fetch("Controllers/TransmittalController.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ids }),
        });

        result = await response.json();
        if (!result.success) {
            Swal.fire(
                "Error",
                "Failed to generate transmittal number.",
                "error"
            );
            return;
        }
    } catch (e) {
        Swal.fire("Error", "Server not responding.", "error");
        return;
    }

    // Use backend-generated transmittal number
    const transmittalId = result.transmittal_id;

    // === STEP 4: Update UI label (Navbar) ===
    const navSpan = document.querySelector(".navbar .left span");
    if (navSpan) navSpan.textContent = transmittalId;

    // Build rows HTML (unchanged)
    let rowsHTML = "";
    let count = 0;

    selected.forEach((row) => {
        const col = row.closest("tr").children;
        count++;
        rowsHTML += `
            <tr>
                <td>${col[1].textContent}</td>
                <td>${col[2].textContent}</td>
                <td>${col[3].textContent}</td>
                <td>${col[4].textContent}</td>
                <td>${col[5].textContent}</td>
                <td>${col[6].textContent}</td>
            </tr>
        `;
    });

    // Current date/time
    const now = new Date();
    const dateStr = now.toLocaleDateString("en-US", {
        month: "numeric",
        day: "numeric",
        year: "numeric",
    });
    const timeStr = now.toLocaleTimeString("en-US");

    // Fetch user name
    const userName = document
        .querySelector(".header-user strong")
        .textContent.trim();

    const content = `
        <div class="paper">
            <div class="voucher-header">
                <div>CITY TREASURER’S OFFICE - Admin Division</div>
                <div>${userName}</div>
                <div>Document Disbursement Voucher</div>
            </div>

            <div class="voucher-subheader">
                <div>Transmittal No: ${transmittalId}</div>
                <div style="text-align:right;">${dateStr} ${timeStr}</div>
            </div>

            <table class="voucher-table">
                <thead>
                    <tr>
                        <th>Control No:</th>
                        <th>Payee</th>
                        <th>Description</th>
                        <th>Fund Type</th>
                        <th>Amount</th>
                        <th>Date In</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHTML}
                </tbody>
            </table>

            <div class="voucher-count">Count: ${count}</div>

            <div class="voucher-receive">
                RECEIVED BY:_________________ DATE:_____________ TIME:_____________
            </div>

            <div class="voucher-footer">
                <div>Document Records Management ver. 1.1<br>Developed by ACLC Students</div>
                <div>Page 1 of 1</div>
            </div>
        </div>
    `;

    const printWindow = window.open("", "", "width=900,height=600");
    printWindow.document.write(`
        <html>
        <head>
            <title>Transmittal Print</title>
            <style>
                @page { size: A4 landscape; margin: 10mm; }
                body { 
                    font-family: serif; 
                    font-size: 13px; 
                }
                .voucher-header { 
                    display: grid; 
                    grid-template-columns: 1fr 1fr 1fr; 
                    text-align: center; 
                    font-weight: bold; 
                    margin-bottom: 6mm;
                }
                .voucher-subheader { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 6mm; 
                }
                .voucher-table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-bottom: 5mm; 
                }
                .voucher-table th{ 
                    border-bottom: 1px solid #000; 
                    padding: 3px 2px; 
                    font-size: 13px; 
                    text-align: left; 
                }
                .voucher-table td { 
                    padding: 3px 2px; 
                    font-size: 13px; 
                }
                .voucher-count { 
                    margin-top: 3mm; 
                    font-size: 13px; 
                    font-weight: bold; 
                }
                .voucher-receive { 
                    margin-top: 25mm; 
                    text-align: right; 
                    font-size: 12px; 
                    font-weight: bold; 
                }
                .voucher-footer { 
                    margin-top: 25mm; 
                    display: flex; 
                    justify-content: space-between; 
                    font-size: 11px; 
                }
            </style>
        </head>
        <body>${content}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
