<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Procurement Tracking</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 20px; }
        .btn { padding: 6px 10px; border-radius: 4px; border: none; cursor: pointer; }
        .btn-sm { font-size: 12px; }
        .btn-primary { background:#0d6efd; color:#fff }
        .btn-warning { background:#ffc107; color:#000 }
        .btn-success { background:#198754; color:#fff }
    </style>
</head>
<body>
    <h1>Procurement Tracking</h1>

    <table id="procurement-table" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Product</th>
                <th>Vendor</th>
                <th>Warehouse</th>
                <th>PO ID</th>
                <th>Quantity Ordered</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
    $(function(){
        var table = $('#procurement-table').DataTable({
            processing: true,
            ajax: {
                url: '/procurement/datatable',
                dataSrc: 'data'
            },
            columns: [
                { data: 'product' },
                { data: 'vendor' },
                { data: 'warehouse' },
                { data: 'purchase_order_id' },
                { data: 'quantity_ordered' },
                { data: 'status' },
                { data: 'actions', orderable: false, searchable: false, render: function(data) {
                    return '<span>' + data + '</span>';
                }}
            ]
        });

        $('#procurement-table').on('click', '.js-advance', function(e){
            e.preventDefault();
            var btn = $(this);
            var step = btn.data('step');
            var po = btn.data('po');
            var product = btn.data('product');

            if (!confirm('Mark this as ' + step + '?')) return;

            $.post('/procurement/update-status', {
                _token: '{{ csrf_token() }}',
                step: step,
                purchase_order_id: po,
                product_id: product
            }).done(function(resp){
                alert(resp.message || 'Updated');
                table.ajax.reload(null, false);
            }).fail(function(xhr){
                var msg = 'Failed to update';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
            });
        });
    });
    </script>
</body>
</html>
