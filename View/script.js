$(document).ready(function () {
    let clientNo = $('#client_no');
    let regex = /^000\d{3}-[A-Z]{5}$/;
    let ibanRegex = /^PL\d{26}$/;

    function getAllDataFromDb() {
        $.ajax({
            url: 'index.php',
            type: 'GET',
            data: { method: 'get_all_data', surname: 'Kowalski', emailDomain: 'gmail.com' },
            success: function (response) {
                response = JSON.parse(response);
                let tableBody = $('#all_data tbody');
                tableBody.empty();
                let tableData = response.tableData.map(function (row) {
                    return [
                        row.name,
                        row.surname,
                        row.email,
                        row.phone,
                        row.client_no,
                        row.account
                    ];
                });
                $('#all_data').bootstrapTable('load', tableData);
                $('#mail_counter').text(response.emailDomainCounter);
                $('#surname_counter').text(response.surnameCounter);
            }
        });
    }

    function restoreDb() {
        $.ajax({
            url: 'index.php',
            type: 'GET',
            data: { method: 'restore_db'}
        });
    }

    $('#get_all_data').on('click', function () {
        getAllDataFromDb();
    });

    $('#restore_db').on('click', function () {
        restoreDb();
    });

    $('#client_no').on('input', function () {
        if (!regex.test(clientNo.val())) {
            clientNo[0].setCustomValidity('Numer klienta musi mieć format 000DDD-WWWWW, gdzie D to cyfra, a W to wielka litera.');
            clientNo[0].reportValidity();
        } else {
            clientNo[0].setCustomValidity(''); // Clear the custom validation message
        }
    });

    $('form').on('submit', function (event) {
        event.preventDefault();
        let form = $(this);
        let data = form.serialize();
        $.ajax({
            url: 'index.php',
            type: 'POST',
            data: data,
            success: function (response) {
                response = JSON.parse(response);
                console.log(response);
                let errors = response.errors ?? [];

                if (errors.length > 0) {
                    let errorsContainer = $('#errors_container');
                    errorsContainer.empty();
                    errorsContainer.append(response.message);
                    let ul = $('<ul></ul>');
                    errors.forEach(function (error) {
                        ul.append('<li>' + error + '</li>');
                    });
                    errorsContainer.append(ul);
                    errorsContainer.show();
                    setTimeout(function () {
                        errorsContainer.fadeOut();
                    }, 10000); // 10 seconds
                } else {
                    let messageContainer = $('#message_container');
                    messageContainer.text(response.message);
                    messageContainer.show();
                    setTimeout(function () {
                        messageContainer.fadeOut();
                    }, 10000); // 10 seconds
                    // form.trigger('reset');
                    getAllDataFromDb();
                }
            }
        });
    });

    $('input[name="choose"]').on('change', function () {
        if ($(this).val() === '1') {
            $('#account-container').removeClass('hide');
            $('#account').attr('required', true).prop('disabled', false);
        } else {
            $('#account-container').addClass('hide');
            $('#account').removeAttr('required').prop('disabled', true);
        }
    });

    $('#account').on('input', function () {
        if (ibanRegex.test($(this).val())) {
            $(this)[0].setCustomValidity(''); // Clear the custom validation message
        } else {
            $(this)[0].setCustomValidity('Numer konta musi mieć format PL i 26 cyfr.');
        }
    });
});