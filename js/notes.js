$(document).ready(function () {
    $('#projects_table').DataTable({
        columnDefs: [
            {orderable: false, targets: [-1]}
        ],
        order: [[2, 'asc']],
        pageLength: 50,
        language: {
            search: "",
            searchPlaceholder: "חיפוש בכל השדות",
            lengthMenu: "הצג _MENU_ בעמוד",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין תקלות",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ תקלות",
            infoFiltered: "(מסונן מתוך _MAX_ תקלות)",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        }
    });
    setTimeout(() => {
        const box = document.getElementById('msg')
        if(box){
            box.style.display = 'none';
        }
    }, 2000);
})