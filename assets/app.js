/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './dropzone/dropzone.css';
import './styles/app.css';

const $ = require('jquery');
require('bootstrap');

$(document).ready(function() {
    $('#add_new_doc_btn').on('click', function () {
        $('#upload_new_document_form').toggle();
    });
    $('.delete-pdf-btn').on('click', function () {
        var filePath = $(this).data('path');
        var filename = $(this).data('filename');

        $.ajax({
            type: "DELETE",
            url: 'http://pdfmanager.test/documents/' + filename,
            success: function (data) {
                console.log(data);
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    });
});
