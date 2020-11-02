/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

<<<<<<< Updated upstream
const $ = require('jquery');
require('bootstrap');

$(document).ready(function() {
=======
const $ = require("jquery");
window.Dropzone = require('dropzone/dist/min/dropzone.min.js');

require('bootstrap');
require('dropzone/dist/min/dropzone.min.css');

window.Dropzone.options.acceptedFiles = "application/pdf";

import './front';

let app = $('#app').App();

$(document).ready(function() {
    $(app).data('plugin_App').start();

>>>>>>> Stashed changes
    $('#add_new_doc_btn').on('click', function () {
        $('#upload_new_document_form').toggle();
    });
    $('.delete-pdf-btn').on('click', function () {

    });
});
