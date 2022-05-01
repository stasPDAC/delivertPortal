$(document).ready(function () {
    $('#projects_table').DataTable({
        columnDefs: [
            {orderable: false, targets: [6]}
        ],
        order: [[5, 'desc']],
        pageLength: 50,
        language: {
            search: "",
            searchPlaceholder: "חיפוש בכל השדות",
            lengthMenu: "הצג _MENU_ בעמוד",
            emptyTable: "אין נתונים זמינים",
            infoEmpty: "אין קבלני ביצוע",
            zeroRecords: "לא נמצאו תוצאות תואמות",
            info: "מציג _START_ עד _END_ מתוך _TOTAL_ קבלני ביצוע",
            infoFiltered: "(מסונן מתוך _MAX_ קבלני ביצוע)",
            paginate: {
                first: "ראשון",
                previous: "קודם",
                next: "הבא",
                last: "אחרון",
            },
        },
        initComplete: function(settings, Json){
            /* Add a select input to filter by status*/
            var container = $('<div style="float:right; margin-right:24px;"></div>')
            var select = $('<select style="margin-right: 24px;"><option value="">הצג את כל הסטטוסים</option></select>');
            container.append(select);
            $('#posts-table_filter').after(container);


            var column = this.api().column(1);
            select.on('change', function () {
                var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                );

                column
                    .search( val ? '^'+val+'$' : '', true, false )
                    .draw();
            } );

            column.data().pluck('stStatus').unique().sort().each( function ( d, j ) {
                select.append( '<option value="'+d+'">'+d+'</option>' )
            } );


        }
    });

    setTimeout(() => {
        const box = document.getElementById('msg')
        box.style.display = 'none';
    }, 2000);
})