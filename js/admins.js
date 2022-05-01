$(document).ready(function () {
    $('#projects_table').DataTable({
        columnDefs: [
            {orderable: false, targets: [4]}
        ],
        order: [[3, 'desc']],
        pageLength: 50,
        language: {
            search: "",
            searchPlaceholder: "חיפוש בכל השדות",
            lengthMenu: "הצג _MENU_ בעמוד",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין מנהלי מערכת",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ מנהלי מערכת",
            infoFiltered: "(מסונן מתוך _MAX_ מנהלי מערכת)",
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