async function printSelectedCheckTransmittal() {
    const selected = [...document.querySelectorAll(".record-checkbox:checked")];

    if (selected.length === 0) {
        Swal.fire("No record selected", "Select at least one row.", "info");
        return;
    }

    const ids = selected.map(cb => cb.value);

    // STEP 1: Fetch full data for each ID
    const rowsData = [];
    try {
        for (let id of ids) {
            const res = await fetch(`Controllers/CheckController.php?action=get_check_print&id=${id}`);
            const data = await res.json();
            if (data.check_date && data.status) {
                rowsData.push(data);
            }
        }
    } catch (err) {
        Swal.fire("Error", "Failed to fetch document data.", "error");
        return;
    }

    if (rowsData.length === 0) {
        Swal.fire("Invalid Selection", "Only records with Check Date can be printed.", "error");
        return;
    }

    // STEP 2: Request Transmittal ID from backend
    let transmittalId;
    try {
        const response = await fetch("Controllers/TransmittalController.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ ids })
        });
        const result = await response.json();
        if (!result.success) {
            Swal.fire("Error", "Failed to generate transmittal number.", "error");
            return;
        }
        transmittalId = result.transmittal_id;
    } catch (e) {
        Swal.fire("Error", "Server not responding.", "error");
        return;
    }

    // STEP 3: Build HTML rows
    let rowsHTML = "";
    rowsData.forEach((row, index) => {
        rowsHTML += `
            <tr>
                <td>${row.control_no}</td>
                <td>${row.payee}</td>
                <td>${row.description ?? "-"}</td>
                <td style="text-align:right;">${row.amount ?? "-"}</td>
                <td>${row.fund_type ?? "-"}</td>
                <td>${row.bank_channel ?? "-"}</td>
                <td>${row.check_date ?? "-"}</td>
                <td>${row.date_out ?? "-"}</td>
                <td>${row.status ?? "-"}</td>
            </tr>
        `;
    });

    // STEP 4: Print
    const now = new Date();
    const dateStr = now.toLocaleDateString("en-US", { month: "numeric", day: "numeric", year: "numeric" });
    const timeStr = now.toLocaleTimeString("en-US");
    const userName = document.querySelector(".header-user strong").textContent.trim();

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
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Fund Type</th>
                        <th>Bank Channel</th>
                        <th>Check Date</th>
                        <th>Date Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>${rowsHTML}</tbody>
            </table>

            <div class="voucher-count">Count: ${rowsData.length}</div>

            <div class="voucher-receive">
                RECEIVED BY:_________________ DATE:_____________ TIME:_____________
            </div>

            <div class="voucher-footer">
                <div>Document Records Management ver. 1.1<br>Developed by ACLC Students</div>
                <div>Page 1 of 1</div>
            </div>
        </div>
    `;

    const printWindow = window.open('', '', 'width=1200,height=700'); // wider window for landscape
    printWindow.document.write(`
        <html>
        <head>
            <title>Check Transmittal Print</title>
            <style>
                @page { size: A4 landscape; margin: 10mm; }
                body { font-family: serif; font-size: 13px; }
                .voucher-header { display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; font-weight: bold; margin-bottom: 6mm; }
                .voucher-subheader { display: flex; justify-content: space-between; margin-bottom: 6mm; }
                .voucher-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
                .voucher-table th { border-bottom: 1px solid #000; padding: 3px 2px; font-size: 13px; text-align: left; }
                .voucher-table td { padding: 3px 2px; font-size: 13px; }
                .voucher-count { margin-top: 3mm; font-size: 13px; font-weight: bold; }
                .voucher-receive { margin-top: 25mm; text-align: right; font-size: 12px; font-weight: bold; }
                .voucher-footer { margin-top: 25mm; display: flex; justify-content: space-between; font-size: 11px; }
            </style>
        </head>
        <body>${content}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}
