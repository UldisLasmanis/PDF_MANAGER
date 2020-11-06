window.$ = window.jQuery = require("jquery");
import swal from 'sweetalert';
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
        this.backBtn = $('<button>', {
            id: 'back_btn',
            type: 'button',
            text: 'Back',
            class: 'btn btn-primary btn-sm',
            style: 'visibility: hidden'
        });

        this.backBtn.on('click', () => {
            this.hideBackBtn();
            this.getDocuments();
        });

        this.addnewDocBtn = $('<button>', {
            id: 'add_new_doc_btn',
            class: 'btn-primary',
            style: 'float:right'
        });
        this.addnewDocBtn.append($('<i>', {
            class: 'fa fa-upload'
        }));

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
            $(this.element).append(this.getUploadBtn());
            $(this.element).append(this.backBtn);
        },
        start: function( text ) {
            let _this = this;
            this.dropzoneForm.dropzone({
                url: "/documents",
                acceptedFiles: "application/pdf",
                init: function () {
                    this.on('complete', function (file) {
                        swal("Success!", "File uploaded!", "success");
                        this.removeFile(file);
                        _this.getDocuments();
                    });

                }
            });
            this.getDocuments();
        },
        getUploadBtn: function()
        {
            Dropzone.autoDiscover = false;

            this.dropzoneFormWrap = $('<div>', {
                id: 'upload_new_document_form',
                style: 'display: none'
            });
            this.dropzoneForm = $('<form>', {
                id: 'myDropzone',
                class: 'dropzone',
                action: '/documents',
                method: 'POST',
                enctype: 'multipart/form-data'
            })
            this.dropzoneFormWrap.append(this.dropzoneForm);

            this.addnewDocBtn.on('click', () => {
                this.dropzoneFormWrap.toggle();
            });

            let content = $('<div>');
            content.append(this.addnewDocBtn);
            content.append(this.dropzoneFormWrap);

            return content;
        },
        getDocuments: function () {
            let _this = this;
            $.get('/documents', function (response) {
                if (!response || !response.data || !response.data.items) {
                    return _this.createLanding([]);
                }
                _this.createLanding(response.data.items);
            })
        },
        createLanding: function (items) {
            let row = $('<div>', {
                class: 'row'
            });

            if (items.length < 4) {
                row.addClass('d-flex justify-content-center')
            }

            for (let i = 0; i < items.length; i++) {
                let currentItemRow = new ItemRow({
                    item: items[i],
                    onDeleteButtonClick: this.onDeleteButtonClick.bind(this),
                    onViewPdfDocButtonClick: this.onViewPdfDocButtonClick,
                    onViewImagesButtonClick: this.onViewImagesButtonClick.bind(this),
                    onAddAttachmentButtonClick: this.onAddAttachmentButtonClick.bind(this)
                });

                row.append(currentItemRow);
            }

            if (!items.length) {
                row.append("No data");
            }

            if (this.row) {
                this.row.replaceWith(row);
                this.row = row;
            } else {
                this.row = row;
                $(this.element).append(this.row);
            }
        },
        onDeleteButtonClick: function (item) {
            let filename = item.filename_hash;
            let _this = this;

            swal({
                title: "Are you sure?",
                text: "PDF file and related files (images, attachment) will be removed!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((doDelete) => {
                if (doDelete) {
                    $.ajax({
                        type: "DELETE",
                        url: '/documents/' + filename,
                        success: function (data) {
                            if (!data || !data.success) {
                                swal("Oops!", data.message, "error");
                                return;
                            }
                            swal("Success!", data.message, "success");
                            _this.getDocuments();
                        }
                    });
                }
            });
        },
        onViewPdfDocButtonClick: function (item) {
            let filename = item.filename_hash;
            $('#embed-src').attr('src', '/documents/' + filename);
        },
        onViewImagesButtonClick: function (item) {
            this.getImages(item.filename_hash)
        },
        onAddAttachmentButtonClick: function (item, uploadInput) {
            this.uploadAttachment(item, uploadInput);
        },
        getImages: function (documentId) {
            let _this = this;
            $.get(`/documents/${documentId}/attachment/previews`, function (response) {
                if (!response || !response.data || !response.data.items) {
                    swal("Oops!", response.message, "error");
                    return;
                }
                _this.viewImages(response.data.items, documentId);
            })
        },
        uploadAttachment: function (item, uploadInput) {
            let _this = this;
            let pdfHashName = item.filename_hash;
            let fd = new FormData();
            let files = uploadInput[0].files;
            if (files.length === 0) {
                swal("Oops!", "File not selected, please try again", "error");
                return;
            }

            fd.append('file',files[0]);

            $.ajax({
                url: `/documents/${pdfHashName}/attachment`,
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function(response){
                    if (!response || !response.success) {
                        swal("Oops!", response.message, "error");
                        return;
                    }
                    swal("Success!", response.message, "success");
                    _this.getDocuments();
                },
            });
        },
        viewImages: function (items, documentName) {
            this.showBackBtn();
            let row = $('<div>', {
                class: 'row'
            });
            if (items.length < 4) {
                row.addClass('d-flex justify-content-center')
            }

            for (let i = 0; i < items.length; i++) {
                let currentItemRow = new ImageItemRow({
                    item: items[i],
                    documentName: documentName
                });

                row.append(currentItemRow);
            }

            if (!items.length) {
                swal("Oops!", "There are no images for this PDF file", "error");
            }

            if (this.row) {
                this.row.replaceWith(row);
                this.row = row;
            } else {
                this.row = row;
                $(this.element).append(this.row);
            }
        },
        showBackBtn: function () {
            $('#back_btn').css('visibility', 'visible');
        },
        hideBackBtn: function () {
            $('#back_btn').css('visibility', 'hidden');
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


function ItemRow (data) {
    this.item = data.item;
    this.onDeleteButtonClick = data.onDeleteButtonClick || function () {};
    this.onViewPdfDocButtonClick = data.onViewPdfDocButtonClick || function () {};
    this.onViewImagesButtonClick = data.onViewImagesButtonClick || function () {};
    this.onAddAttachmentButtonClick = data.onAddAttachmentButtonClick || function () {};
    this.elementWrap = null;
    this.buttonWrap = null;
    this.buttonDelete = null;
    this.uploadInput = null;
    // this.rowCol = $('<div>', {class: 'col-sm-2'});

    this.init();

    return this.elementWrap;
}

$.extend( ItemRow.prototype, {
    init: function () {
        this.createWrapper();
        this.createButtons();
        this.createMetaData();
    },
    createWrapper: function () {
        this.elementWrap = $('<div>', {class: "col-sm-3 pdf-item"})
        this.elementWrap.append($('<img>',
            {src: this.item.preview_image_path, class: "pdf-image", width: '100%'})
        )
        this.buttonWrap = $('<div>' , {class: 'row d-flex justify-content-center'})
    },
    getRowColWrapper() {
        return $('<div>', {class: 'col-sm-2'});
    },
    createButtons: function () {
        this.createDeleteButton();
        this.createViewButton();
        this.createViewImagesButton();
        if (this.item.attachment_id === null) {
            this.createEnabledAddAttachmentButton();
            this.createDisabledDownloadAttachmentButton();
        } else {
            this.createDisabledAddAttachmentButton();
            this.createEnabledDownloadAttachmentButton();
        }
        this.elementWrap.append('<div>', {
            class: 'row d-flex justify-content-center'
        }).append(this.buttonWrap);
    },
    createDeleteButton: function () {
        this.buttonDelete = $('<button>', {
            class: "btn btn-danger btn-sm",
            title: "Delete PDF and related files"
        });
        this.buttonDelete.append($('<i>', {class: "fa fa-trash"}))
        this.buttonDelete.on('click', () => {this.onDeleteButtonClick(this.item)});
        this.buttonWrap.append(this.getRowColWrapper().append(this.buttonDelete))
    },
    createViewButton: function () {
        this.buttonView = $('<button>', {
            class: "btn btn-light btn-sm",
            title: "View PDF",
            "data-toggle": "modal",
            "data-target": "#pdf_viewer_modal"
        })
        this.buttonView.append($('<i>', {class: "fa fa-eye"}))
        this.buttonView.on('click', () => {this.onViewPdfDocButtonClick(this.item)})

        this.buttonWrap.append(this.getRowColWrapper().append(this.buttonView))
    },
    createViewImagesButton: function () {
        this.buttonViewImages = $('<button>', {
            class: "btn btn-dark btn-sm",
            title: "View images"
        });
        this.buttonViewImages.append($('<i>', {class: "fa fa-list"}))
        this.buttonViewImages.on('click', () => {
            this.onViewImagesButtonClick(this.item);
        });
        this.buttonWrap.append(this.getRowColWrapper().append(this.buttonViewImages))
    },
    createEnabledAddAttachmentButton: function () {
        this.buttonAddAttachment = $('<label>', {
            class: "btn btn-success btn-sm",
            title: "Add attachment"
        });
        this.buttonAddAttachment.append($('<i>', {class: "fa fa-upload"}))
        this.uploadInput = $('<input>', {
            type: 'file',
            hidden: true
        });
        this.buttonAddAttachment.append(this.uploadInput);
        this.buttonAddAttachment.on('change', () => {
            this.onAddAttachmentButtonClick(this.item, this.uploadInput);
        });
        this.buttonWrap.append(this.getRowColWrapper().append(this.buttonAddAttachment))
    },
    createDisabledAddAttachmentButton: function () {
        this.buttonAddAttachment = $('<label>', {
            class: "btn btn-secondary btn-sm",
            title: "Attachment already added"
        });
        this.buttonAddAttachment.append($('<i>', {class: "fa fa-upload"}))
        this.buttonAddAttachment.on('click', () => {
            swal('Attachment already added!', 'You can add only one attachment per file!', 'info')
        });
        this.buttonWrap.append(this.getRowColWrapper().append(this.buttonAddAttachment))
    },
    createEnabledDownloadAttachmentButton: function () {
        this.buttonDownloadAttachment = $('<a>', {
            class: "btn btn-primary btn-sm",
            href: `/documents/${this.item.filename_hash}/attachment`,
            title: "Download attachment",
        });
        this.buttonDownloadAttachment.append($('<i>', {class: "fa fa-download"}))
        this.buttonWrap.append(this.getRowColWrapper().append(this.buttonDownloadAttachment))
    },
    createDisabledDownloadAttachmentButton: function () {
        this.buttonDownloadAttachment = $('<a>', {
            class: "btn btn-secondary btn-sm inactive-a-href-btn",
            href: "#",
            title: "No attachment to download"
        });
        this.buttonDownloadAttachment.on('click', function() {
            swal('Notice!', 'There is no attachment to download!', 'info')
        })
        this.buttonDownloadAttachment.append($('<i>', {class: "fa fa-download"}))
        this.buttonWrap.append(this.getRowColWrapper().append(this.buttonDownloadAttachment))
    },
    createMetaData: function () {
        this.elementWrap.append(
            $('<div>', {class: "additional-info"})
                .append($("<i>", {
                    text: this.item.filename_original,
                    style: 'font-weight: bold',
                    title: this.item.filename_original
                }))
                .append($("<br>"))
                .append($("<i>").text(`Added at: ${this.item.uploaded_at}`))
                .append($("<br>"))
                .append($("<i>").text(`Pages: ${this.item.page_cnt}`))
                .append($("<br>"))
                .append($("<i>").text(`Size: ${this.item.size_in_KB} KB`))
        )
    }

});

function ImageItemRow (data) {
    this.item = data.item;
    this.pdfName = data.pdfName;
    this.elementWrap = null;

    this.init();

    return this.elementWrap;
}

$.extend( ImageItemRow.prototype, {
    init: function () {
        this.createWrapper();
        this.createButtons();
        this.createMetaData();
    },
    createWrapper: function () {
        this.elementWrap = $('<div>', {class: "col-sm-3 pdf-item"});
        this.elementWrap.append($('<img>', {
            src: this.item.file_path,
            alt: this.item.filename,
            style: "width: 100%"
        }));
    },
    createButtons: function () {
        this.downloadImageButton = $('<div>', {
            class: 'download-btn-wrap'
        }).append($('<a>', {
            class: "btn btn-primary btn-sm view-pdf-btn",
            href: `/documents/${this.pdfName}/attachment/previews/${this.item.filename}`
        }).append($('<i>', {class: "fa fa-download"})))

        this.elementWrap.append(this.downloadImageButton);
    },
    createMetaData: function () {
        this.elementWrap
            .append($('<div>', {class: "additional-info"})
            .append($("<i>").text(`Added at: ${this.item.uploaded_at}`))
            .append($("<br>"))
            .append($("<i>").text(`Pages: ${this.item.page_nr}`))
            .append($("<br>"))
            .append($("<i>").text(`Size: ${this.item.size_in_KB} KB`))
            .append($("<br>"))
            .append($("<br>"))
        )
    }

});