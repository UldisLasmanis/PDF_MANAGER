window.$ = window.jQuery = require("jquery");
// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;( function( $, window, document, undefined ) {

    "use strict";

    // undefined is used here as the undefined global variable in ECMAScript 3 is
    // mutable (ie. it can be changed by someone else). undefined isn't really being
    // passed in so we can ensure the value of it is truly undefined. In ES5, undefined
    // can no longer be modified.

    // window and document are passed through as local variables rather than global
    // as this (slightly) quickens the resolution process and can be more efficiently
    // minified (especially when both are regularly referenced in your plugin).

    // Create the defaults once
    var pluginName = "App",
        defaults = {
            propertyName: "value"
        };

    // The actual plugin constructor
    function Plugin ( element, options ) {
        this.element = element;
        this.row = null;
        this.uplaodBtnRow = '<div>\n' +
            '            <button id="add_new_doc_btn" class="btn-primary" style="float:right">\n' +
            '                <i class="fa fa-upload"></i>\n' +
            '            </button>\n' +
            '            <div style="display: none" id="upload_new_document_form">\n' +
            '                <form action="/documents"\n' +
            '                      class="dropzone"\n' +
            '                      method="POST" enctype="multipart/form-data"\n' +
            '                      id="myDropzone">\n' +
            '                </form>\n' +
            '            </div>\n' +
            '        </div>'

        // jQuery has an extend method which merges the contents of two or
        // more objects, storing the result in the first object. The first object
        // is generally empty as we don't want to alter the default options for
        // future instances of the plugin
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    // Avoid Plugin.prototype conflicts
    $.extend( Plugin.prototype, {
        init: function() {
        },
        start: function( text ) {
            let _this = this;
            $(this.element).append(this.uplaodBtnRow);
            $("form#myDropzone").dropzone({
                url: "/documents",
                acceptedFiles: "application/pdf",
                init: function () {
                    this.on('complete', function (file) {
                        this.removeFile(file);
                        _this.getDocuments();
                    });

                }
            });

            this.getDocuments();
        },
        getDocuments: function () {
            let _this = this;
            $.get('/documents', function (response) {
                if (!response || !response.data || !response.data.items) {
                    return;
                }
                _this.createLanding(response.data.items);
            })
        },
        createLanding: function (items) {
            let row = $('<div>', {
                class: 'row'
            });

            for (let i = 0; i < items.length; i++) {
                let elementWrap = $('<div>', {
                    class: "col-sm-3 pdf-item"
                })
                elementWrap.append($('<img>', {
                    src: items[i].thumbnail_path,
                    class: "pdf-thumbnail",
                    width: "100%"
                }))
                let buttonView = $('<button>', {
                    class: "btn btn-info btn-sm view-pdf-btn",
                    "data-toggle": "modal",
                    "data-target": "#pdf_viewer_modal",
                    "data-path": items[i].file_path

                });
                buttonView.on('click', function () {
                    $('#embed-src').attr('src', items[i].file_path);
                })
                elementWrap.append(buttonView.append($('<i>', {
                    class: "fa fa-eye"
                })))
                let buttonDelete = $('<button>', {
                    class: "btn btn-danger btn-sm delete-pdf-btn",
                    "data-filename": items[i].md5_filename,
                    "data-path": items[i].file_path

                });
                buttonDelete.on('click', function () {
                    let filePath = $(this).data('path');
                    let filename = $(this).data('filename');

                    $.ajax({
                        type: "DELETE",
                        url: '/documents/' + filename,
                        success: function (data) {
                            console.log(data);
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                });
                elementWrap.append(buttonDelete
                    .append($('<i>', {
                    class: "fa fa-trash"
                })))

                let buttonAttachments = $('<button>', {
                    class: "btn btn-danger btn-sm "
                });
                buttonAttachments.on('click', () => {
                    this.getAttachments(items[i].md5_filename)
                });
                elementWrap.append(buttonAttachments
                    .append($('<i>', {
                        class: "fa fa-close"
                    })))
                elementWrap.append(
                    $('<div>', {
                        class: "additional-pdf-info"
                    })
                    .append($("<b>").text(items[i].original_filename))
                    .append($("<br>"))
                    .append($("<i>").text(`Added at: ${items[i].uploaded_at}`))
                    .append($("<br>"))
                    .append($("<i>").text(`Pages: ${items[i].page_cnt}`))
                    .append($("<br>"))
                    .append($("<i>").text(`Size: ${items[i].size_in_KB} KB`))

                )
                row.append(elementWrap);
            }

            if (this.row) {
                this.row.replaceWith(row);
                this.row = row;
            } else {
                this.row = row;
                $(this.element).append(this.row);
            }
        },
        getAttachments: function (documentId) {
            let _this = this;
            $.get(`/documents/${documentId}/attachment/previews`, function (response) {
                if (!response || !response.data || !response.data.items) {
                    return;
                }
                _this.createAttachmentPreview(response.data.items);
            })
        },
        createAttachmentPreview: function (items) {
            let row = $('<div>', {
                class: 'row'
            }).append($('<button>').html('back').on('click',  () => {
                this.getDocuments();
            }));

            console.log(items);

            // for (let i = 0; i < items.length; i++) {
            //
            // }

            if (this.row) {
                this.row.replaceWith(row);
                this.row = row;
            } else {
                this.row = row;
                $(this.element).append(this.row);
            }

        }
    } );

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[ pluginName ] = function( options ) {
        return this.each( function() {
            if ( !$.data( this, "plugin_" + pluginName ) ) {
                $.data( this, "plugin_" +
                    pluginName, new Plugin( this, options ) );
            }
        } );
    };

} )( jQuery, window, document );
