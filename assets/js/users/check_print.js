async function printSelectedCheckTransmittal() {
    const selected = [...document.querySelectorAll(".record-checkbox:checked")];

    if (selected.length === 0) {
        Swal.fire("No record selected", "Select at least one row.", "info");
        return;
    }

    // === STEP 1: Collect selected IDs ===
    const ids = selected.map(cb => cb.value);

    // === STEP 2: Request new Transmittal ID + Update DB ===
    let result;
    try {
        const response = await fetch("Controllers/TransmittalController.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ids })
        });

        result = await response.json();
        if (!result.success) {
            Swal.fire("Error", "Failed to generate check transmittal number.", "error");
            return;
        }

    } catch (e) {
        Swal.fire("Error", "Server not responding.", "error");
        return;
    }

    // Use backend-generated transmittal number
    const transmittalId = result.transmittal_id;

    // === STEP 3: Update UI label (Navbar) ===
    const navSpan = document.querySelector(".navbar .left span");
    if (navSpan) navSpan.textContent = transmittalId;

    // === STEP 4: Build rows HTML ===
    let rowsHTML = "";
    let count = 0;

    selected.forEach(row => {
        const col = row.closest("tr").children;
        count++;

        const controlNo = col[1].textContent.trim();
        const payee     = col[2].textContent.trim();
        const bank      = col[3].textContent.trim();
        const checkDate = col[4].textContent.trim();
        const status    = col[5].textContent.trim();

        // Only valid rows
        if (checkDate !== "-" && status !== "") {
            rowsHTML += `
                <tr>
                    <td>${controlNo}</td>
                    <td>${payee}</td>
                    <td>${bank}</td>
                    <td>${checkDate}</td>
                    <td>${status}</td>
                </tr>
            `;
        }
    });

    if (rowsHTML === "") {
        Swal.fire("Invalid Selection", "Only records with Check Date can be printed.", "error");
        return;
    }

    // === STEP 5: Current date/time ===
    const now = new Date();
    const dateStr = now.toLocaleDateString("en-US", { month: "numeric", day: "numeric", year: "numeric" });
    const timeStr = now.toLocaleTimeString("en-US");

    // === STEP 6: Fetch user name ===
    const userName = document.querySelector(".header-user strong").textContent.trim();

    // === PRINT TEMPLATE ===
    const content = `
        <div class="paper">
            <div class="voucher-header">
                <div>CITY TREASURER’S OFFICE - Admin Division</div>
                <div>${userName}</div>
                <div>Check Transmittal</div>
            </div>

            <div class="voucher-subheader">
                <div>Transmittal No: ${transmittalId}</div>
                <div style="text-align:right;">${dateStr} ${timeStr}</div>
            </div>

            <table class="voucher-table">
                <thead>
                    <tr>
                        <th>Control No.</th>
                        <th>Payee</th>
                        <th>Bank Channel</th>
                        <th>Check Date</th>
                        <th>Status</th>
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

    // === STEP 7: Print ===
    const printWindow = window.open('', '', 'width=900,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Check Transmittal Print</title>
            <style>
                @page { size: A4; margin: 10mm; }
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
                .voucher-table th { 
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
