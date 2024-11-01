jQuery(document).ready(function ($) {
    $('a[href="admin.php?page=sprouted-fb-community"]').attr('target', '_blank');
    $("#setting-save").submit(function (e) {
        var is_enable = $("#setting-save #sproutedwebchat_active option:selected").val();
        var _nonce = $("#setting-save input[name=_nonce]").val();
        $.ajax({
            type: "POST",
            url: sproutedweb.ajax_url,
            dataType: "json",
            data: {action: 'sprouted_setting_save', _nonce: _nonce, is_enable: is_enable},
            beforeSend: function () {
                $('.sproutedweb').showLoading();
            },
            complete: function () {
            },
            success: function (response) {
                $('.sproutedweb').hideLoading();
                $('.sproutedweb .sproutedweb-message').removeClass('error').addClass('updated').show().html('<p>' + response.message + '</p>');
                setTimeout(function () {
                    location.reload();
                }, 2000);
            },
            error: function (request, status, error) {
                alert(status);
            }
        });
        e.preventDefault();
    });

    $("#verify-key").submit(function (e) {
        var license_key = $("#verify-key input[name=key]").val();
        var _nonce = $("#verify-key input[name=_nonce]").val();
        if (license_key && _nonce) {
            $.ajax({
                type: "POST",
                url: sproutedweb.ajax_url,
                dataType: "json",
                data: {action: 'sprouted_license_verify', _nonce: _nonce, license_key: license_key},
                beforeSend: function () {
                    $('.sproutedweb').showLoading();
                },
                complete: function () {
                },
                success: function (response) {
                    $('.sproutedweb').hideLoading();
                    if (response && response.status == 1) {
                        $('.sproutedweb .sproutedweb-message').removeClass('error').addClass('updated').show().html('<p>' + response.message + '</p>');
                        setTimeout(function () {
                            window.location.href = sproutedweb.features_page;
                        }, 2000);
                    } else {
                        $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>' + response.message + '</p>');
                    }
                },
                error: function (request, status, error) {
                    alert(status);
                }
            });
        } else {
            $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>Enter License Key</p>');
        }
        e.preventDefault();
    });


    $('body').on('change', '.license-action', function () {
        var elem = $(this);
        var site_url = elem.data('site_url');
        var index = parseInt(elem.attr('data-index'));
        var key_status = parseInt(elem.attr('data-key_status'));
        var val = parseInt(elem.val());


        if (key_status) {
            var confirmTxt = 'Are you sure you want to Deactivate and Remove this site from your license?';

        } else {
            var confirmTxt = 'Are you sure you want to activate this site?';
        }
        var r = confirm(confirmTxt);
        if (r == false) {
            // alert('input[name=status-'+index+'][value='+key_status+']');

            $('input[name=status-' + index + '][value=' + (val ? 0 : 1) + ']').prop('checked', true);
            return false;
        }
        if (site_url) {
            $.ajax({
                type: "POST",
                url: sproutedweb.ajax_url,
                dataType: "json",
                data: {action: 'sprouted_license_deactivate', site_url: site_url, key_status: key_status},
                beforeSend: function () {
                    $('.license-log').showLoading({
                        'addClass': 'loading-indicator-bars',
                        waitingText: 'Please wait as we deactivate and remove this site from your license.'
                    });
                },
                complete: function () {
                },
                success: function (response) {
                    $('.license-log').hideLoading();
                    if (response && response.status == 1) {
                        $('.sproutedweb .sproutedweb-message').removeClass('error').addClass('updated').show().html('<p>' + response.message + '</p>');
                        if (key_status == 1) {
                            $('input[name=status-' + index + ']').attr('data-key_status', val);
                            $('.license-status-' + index).find('b').css({'color': 'red'}).html('Inactive');
                        } else {
                            $('.license-status-' + index).find('b').css({'color': 'green'}).html('Active');
                        }
                        $('input[name=status-' + index + '][value=' + (val ? 1 : 0) + ']').prop('checked', true);
                        setTimeout(function () {
                            if (response.haveMyDomain == 0) {
                                window.location.href = sproutedweb.setting_page;
                            } else {
                                location.reload();
                            }
                        }, 2000);
                    } else {
                        $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>' + response.message + '</p>');
                    }
                },
                error: function (request, status, error) {
                    alert(status);
                    $('input[name=status-' + index + '][value=' + (val ? 0 : 1) + ']').prop('checked', true);
                }
            });
        } else {
            $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>Enter License Key</p>');
        }
    });


    $('body').on('click', '#gtmetrix-history-section .pagination2 a', function () {
        var page_no = $(this).data('page');
        if (page_no) {
            $.ajax({
                type: "POST",
                url: sproutedweb.ajax_url,
                dataType: "json",
                data: {action: 'sprouted_gtmetrix_history', page_no: page_no, _nonce: sproutedweb._nonce},
                beforeSend: function () {
                    $('#gtmetrix-history-section').showLoading();
                },
                complete: function () {
                },
                success: function (response) {
                    $('#gtmetrix-history-section').hideLoading();
                    if (response.status == 1) {
                        $('#gtmetrix-history-section').html(response.html);
                    } else {
                        $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>' + response.message + '</p>');
                        $("html, body").animate({scrollTop: 0}, "slow");
                    }
                },
                error: function (request, status, error) {
                    alert(status);
                }
            });
        } else {
            $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>Invalid Page No.</p>');
            $("html, body").animate({scrollTop: 0}, "slow");
        }
    });
    $('body').on('click', '#gtmetrix-scan-history .download-full-report', function () {
        var report_url = $(this).data('full_report');
        var testid = $(this).data('testid');
        if (report_url && testid) {
            $.ajax({
                type: "POST",
                url: sproutedweb.ajax_url,
                dataType: "html",
                data: {action: 'sprouted_gtmetrix_download_report', report_url: report_url, testid: testid},
                beforeSend: function () {
                    $('#gtmetrix-history-section').showLoading({'addClass': 'loading-indicator-bars'});
                },
                complete: function () {
                },
                success: function (response) {
                    $('#gtmetrix-history-section').hideLoading();
                    console.log(response.status);
                    var json = $.parseJSON(response);
                    if (json.status) {
                        if (json.hasOwnProperty('report')) {
                            var a = document.createElement("a");
                            a.href = 'data:application/pdf;base64,' + json.report;
                            a.download = 'report_pdf-' + testid + ".pdf"; //update for filename
                            document.body.appendChild(a);
                            a.click();
                            // remove `a` following `Save As` dialog,
                            // `window` regains `focus`
                            window.onfocus = function () {
                                document.body.removeChild(a)
                            }
                        }
                        $.ajax({
                            type: "POST",
                            url: sproutedweb.ajax_url,
                            dataType: "html",
                            data: {action: 'sprouted_gtmetrix_scan_result'},
                            beforeSend: function () {
                                $('.gtmetrix-section').showLoading();
                            },
                            complete: function () {
                            },
                            success: function (response) {
                                $('.gtmetrix-section').hideLoading();
                                $('#wpbody-content').html(response);
                            },
                            error: function (request, status, error) {
                                alert(status);
                            }
                        });
                    } else {
                        $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>' + json.message + '</p>');
                        $("html, body").animate({scrollTop: 0}, "slow");
                    }
                },
                error: function (request, status, error) {
                    alert(status);
                }
            });
        } else {
            $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>Report URL missing.</p>');
            $("html, body").animate({scrollTop: 0}, "slow");
        }
    });

    function validURL(myURL) {
        return /^(http(s)?:\/\/)?(www\.)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/.test(myURL);
    }

    $('body').on('click', '.gtmetrix-section button', function () {
        var elem = $(this);
        var scan_url = $(".gtmetrix-section input[name=scan_url]").val();
        console.log(validURL(scan_url));
        if (validURL(scan_url) === false) {
            $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>Error: Please Enter a valid URL to test.</p>');
            return false;
        }
        var scan_location = $(".gtmetrix-section select[name=location] option:selected").val();
        var scan_browser = $(".gtmetrix-section select[name=browser] option:selected").val();
        var _nonce = $(".gtmetrix-section input[name=_nonce]").val();
        $.ajax({
            type: "POST",
            url: sproutedweb.ajax_url,
            dataType: "json",
            data: {
                action: 'sprouted_gtmetrix_scan',
                scan_url: scan_url,
                scan_location: scan_location,
                scan_browser: scan_browser,
                _nonce: _nonce
            },
            beforeSend: function () {
                $('.gtmetrix-section').showLoading({
                    'addClass': 'loading-indicator-bars',
                    waitingText: 'Performing Scan Now, Please Wait'
                });
            },
            complete: function () {
            },
            success: function (response) {
                $('.gtmetrix-section').hideLoading();
                if (response && response.status == 1) {
                    $('.sproutedweb .sproutedweb-message').removeClass('error').addClass('updated').show().html('<p>' + response.message + '</p>');

                    $.ajax({
                        type: "POST",
                        url: sproutedweb.ajax_url,
                        dataType: "html",
                        data: {action: 'sprouted_gtmetrix_scan_result'},
                        beforeSend: function () {
                            $('.gtmetrix-section').showLoading();
                        },
                        complete: function () {
                        },
                        success: function (response) {
                            $('.gtmetrix-section').hideLoading();
                            $('#wpbody-content').html(response);
                        },
                        error: function (request, status, error) {
                            alert(status);
                        }
                    });
                } else {
                    if (response.is_free == 0) {
                        setTimeout(function () {
                            $.ajax({
                                type: "POST",
                                url: sproutedweb.ajax_url,
                                dataType: "html",
                                data: {action: 'sprouted_gtmetrix_scan_result'},
                                beforeSend: function () {
                                    $('.gtmetrix-section').showLoading();
                                },
                                complete: function () {
                                },
                                success: function (response) {
                                    $('.gtmetrix-section').hideLoading();
                                    $('#wpbody-content').html(response);
                                },
                                error: function (request, status, error) {
                                    alert(status);
                                }
                            });
                        }, 2000);
                    }
                    $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>' + response.message + '</p>');
                }
            },
            error: function (request, status, error) {
                alert(status);
            }
        });
    });

    $('body').on('change', '#gtmetrix-package-section select[name=gtmetrix-packages]', function () {
        var url = $(this).val();
        if (url) {
            // window.open(url,'_blank');
            window.location.href = url;
        }
    });

    $('body').on('click', '.gtmetrix-key-section button', function () {
        var license_key = $(".gtmetrix-key-section input[name=gtmetrix-key]").val();
        var _nonce = $(".gtmetrix-key-section input[name=_nonce]").val();
        if (license_key && _nonce) {
            $.ajax({
                type: "POST",
                url: sproutedweb.ajax_url,
                dataType: "json",
                data: {action: 'sprouted_gtmetrix_verify', _nonce: _nonce, license_key: license_key},
                beforeSend: function () {
                    $('.gtmetrix-key-section').showLoading();
                },
                complete: function () {
                },
                success: function (response) {
                    $('.gtmetrix-key-section').hideLoading();
                    if (response && response.status == 1) {
                        $('.sproutedweb .sproutedweb-message').removeClass('error').addClass('updated').show().html('<p>' + response.message + '</p>');
                        setTimeout(function () {
                            window.location.href = sproutedweb.sprouted_scan;
                        }, 2000);
                    } else {
                        $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>' + response.message + '</p>');
                    }
                },
                error: function (request, status, error) {
                    $('.gtmetrix-key-section').hideLoading();
                    alert(status);
                }
            });
        } else {
            $('.sproutedweb .sproutedweb-message').removeClass('updated').addClass('error').show().html('<p>Enter License Key</p>');
        }
    });

});