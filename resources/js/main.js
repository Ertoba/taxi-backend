$(document).ready(function () {
    $('#payoutDriver').select2({
        minimumInputLength: 3,
        ajax: {
            url: payoutVendorSearchUrl, // Defined globally below
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (drivers) {
                        return {
                            id: drivers.id,
                            text: drivers.name,
                        };
                    })
                };
            },
            cache: true,
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error while fetching vendor data:", textStatus, errorThrown);
            }
        }
    });

    // Preload if vendor is set
    var vendorId = $('#payoutDriver').data('vendor-id');
    var vendorName = $('#payoutDriver').data('vendor-name');

    if (vendorId && vendorName) {
        var preloadOption = new Option(vendorName, vendorId, true, true);
        $('#payoutDriver').append(preloadOption).trigger('change');
    }

    $('.open-payout-modal').on("click", function (e) {
                e.preventDefault();
                const payoutId = $(this).data('payout-id');
                const amount = $(this).data('amount');
                const vendor = $(this).data('vendor');
                const status = $(this).data('status');
                $('#modalPayoutId').val(payoutId);
                $('#modalAmount').text(amount);
                $('#modalVendor').text(vendor);
                $('#status').text('Success');
                $('#payoutModal').modal('show');
            });

            // Submit form with file + notes
            $('#payoutForm').on("submit", function (e) {
                e.preventDefault();
                const payoutId = $('#modalPayoutId').val();
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("admin.payouts.updateStatus", ":payoutId") }}'.replace(':payoutId', payoutId),
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        $('#payoutModal').modal('hide');
                        Swal.fire("Success", "Payout released successfully!", "success").then(() => {
                            window.location.reload();
                        });
                    },
                    error: function (response) {
                        Swal.fire("Error", "Something went wrong.", "error");
                    }
                });
            });
});
