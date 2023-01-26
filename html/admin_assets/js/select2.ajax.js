// SELECT2
$(".select2").select2({
    placeholder: "@Select Searchable Data",
});


// SELECT2
$(".select2-icon").select2({
    placeholder: "@Select Searchable Data",
    //minimumResultsForSearch: 1 / 0,
    templateResult: icon,
    templateSelection: icon,
    escapeMarkup: function(elm) {
        return elm
    }
});
function icon(elm) {
    elm.element;
    var opt_txt = "<i class='"+$(elm.element).data("icon")+" mr-2' style='color:"+$(elm.element).data("color")+"'></i>";
    opt_txt += elm.text; 
    return elm.id ? opt_txt : elm.text
}



/*
    :: 주의 
    :: ajax return 값에 data.item  내에 '@id' 반드시 포함
    :: Because @id 값 기반 aria-select element 생성
*/
$(".sel-ajax-people").select2({
    ajax: {
        url: '/api/select/people',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoPP,
    templateSelection: formatRepoSelectionPP
});
function formatRepoPP(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionPP(repo);
    return markup;
}
function formatRepoSelectionPP(repo) {
    return repo.pp_name || repo.text;
}
function wrapperOptionPP(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" +data.pp_name+ "</div>";
    if (data.pp_memo) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" +data.pp_memo+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-envelope'></i> " +data.pp_email+ "</div>";
    markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-building'></i> " +data.c_name+ "</div>";
    markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " +data.pp_dept+ "</div>";
    markup += "</div></div></div>";
    return markup;
}



// 자산명 조회
$(".sel-ajax-assets").select2({
    ajax: {
        url: '/api/select/assets',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoAS,
    templateSelection: formatRepoSelectionAS
});
function formatRepoAS(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionAS(repo);
    return markup;
}
function formatRepoSelectionAS(repo) {
    return repo.am_name || repo.text;
}
function wrapperOptionAS(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" +data.am_name+ "</div>";
    if (data.am_memo) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" +data.am_memo+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " +data.am_models_name+ "</div>";
    markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-layer-group'></i> " +data.am_vmware_name+ "</div>";
    markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " +data.am_serial_no+ "</div>";
    markup += "</div></div></div>";
    return markup;
}



/*
    :: 주의 
    :: ajax return 값에 data.item  내에 '@id' 반드시 포함
    :: Because @id 값 기반 aria-select element 생성
*/
$(".sel-ajax-aim").select2({
    ajax: {
        url: '/api/select/empty_ip_assets',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoAIM,
    templateSelection: formatRepoSelectionAIM
});
function formatRepoAIM(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionAIM(repo);
    return markup;
}
function formatRepoSelectionAIM(repo) {
    return repo.am_name || repo.text;
}
function wrapperOptionAIM(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" + data.am_name+ "</div>";
    if (data.am_memo) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" + data.am_memo+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " + data.am_models_name+ "</div>";
    markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-map-marker-alt'></i> " + data.am_rack_code + "</div>";
    markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " + data.am_serial_no + "</div>";
    markup += "</div></div></div>";
    return markup;
}

/*
    :: 주의 
    :: ajax return 값에 data.item  내에 '@id' 반드시 포함
    :: Because @id 값 기반 aria-select element 생성
*/
$(".sel-ajax-vim").select2({
    ajax: {
        url: '/api/select/vmware_assets',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoVIM,
    templateSelection: formatRepoSelectionVIM
});
function formatRepoVIM(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionVIM(repo);
    return markup;
}
function formatRepoSelectionVIM(repo) {
    return repo.am_vmware_name || repo.text;
}
function wrapperOptionVIM(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" + data.am_vmware_name+ "</div>";
    if (data.am_memo) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" + data.am_memo+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " + data.am_models_name+ "</div>";
    markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-map-marker-alt'></i> " + data.am_rack_code + "</div>";
    markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " + data.am_serial_no + "</div>";
    markup += "</div></div></div>";
    return markup;
}

/*
    :: 주의 
    :: ajax return 값에 data.item  내에 '@id' 반드시 포함
    :: Because @id 값 기반 aria-select element 생성
*/
$(".sel-ajax-dim").select2({
    ajax: {
        url: '/api/select/direct',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoDIM,
    templateSelection: formatRepoSelectionDIM
});
function formatRepoDIM(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionDIM(repo);
    return markup;
}
function formatRepoSelectionDIM(repo) {
    return repo.am_name || repo.text;
}
function wrapperOptionDIM(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" + data.am_name+ "</div>";
    if (data.am_memo) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" + data.am_memo+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " + data.am_models_name+ "</div>";
    markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-map-marker-alt'></i> " + data.am_rack_code + "</div>";
    markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " + data.am_serial_no + "</div>";
    markup += "</div></div></div>";
    return markup;
}


/*
    :: 주의 
    :: ajax return 값에 data.item  내에 '@id' 반드시 포함
    :: Because @id 값 기반 aria-select element 생성
*/
$(".sel-ajax-idrac").select2({
    ajax: {
        url: '/api/select/not_idrac',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoIDrac,
    templateSelection: formatRepoSelectionIDrac
});
function formatRepoIDrac(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionIDrac(repo);
    return markup;
}
function formatRepoSelectionIDrac(repo) {
    return repo.am_name || repo.text;
}
function wrapperOptionIDrac(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" + data.am_name+ "</div>";
    if (data.am_memo) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" + data.am_memo+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " + data.am_models_name+ "</div>";
    markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-map-marker-alt'></i> " + data.am_rack_code + "</div>";
    markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " + data.am_serial_no + "</div>";
    markup += "</div></div></div>";
    return markup;
}



/*
    :: 주의 
    :: ajax return 값에 data.item  내에 '@id' 반드시 포함
    :: Because @id 값 기반 aria-select element 생성
*/
$(".sel-ajax-rack").select2({
    ajax: {
        url: '/api/select/rack',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoRack,
    templateSelection: formatRepoSelectionRack
});
function formatRepoRack(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionRack(repo);
    return markup;
}
function formatRepoSelectionRack(repo) {
    return repo.r_code || repo.text;
}
function wrapperOptionRack(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" + data.r_code+ "</div>";
    if (data.l_address) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" + data.l_address+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " + data.l_name+ "</div>";
    markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-map-marker-alt'></i> " + data.l_code+ "</div>";
    markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " + data.l_manager_name+ "</div>";
    markup += "</div></div></div>";
    return markup;
}




/*
    :: 주의 
    :: ajax return 값에 data.item  내에 '@id' 반드시 포함
    :: Because @id 값 기반 aria-select element 생성
*/
$(".sel-ajax-service").select2({
    ajax: {
        url: '/api/select/service',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 1,
    templateResult: formatRepoServ,
    templateSelection: formatRepoSelectionServ
});
function formatRepoServ(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionServ(repo);
    return markup;
}
function formatRepoSelectionServ(repo) {
    return repo.vms_name || repo.text;
}
function wrapperOptionServ(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" + data.vms_name+ "</div>";
    if (data.vms_memo) {
        markup += "<div class='select2-result-repository__description fs-xs opacity-80 mb-1'>" + data.vms_memo+ "</div>";
    }
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " + data.ip_address+ "</div>";
    //markup += "<div class='select2-result-repository__stargazers mr-2'><i class='fal fa-map-marker-alt'></i> " + data. + "</div>";
    //markup += "<div class='select2-result-repository__watchers mr-2'><i class='fal fa-tag'></i> " + data.am_serial_no + "</div>";
    markup += "</div></div></div>";
    return markup;
}


$(".sel-ajax-account").select2({
    ajax: {
        url: '/api/select/account',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 2,
    templateResult: formatRepoAC,
    templateSelection: formatRepoSelectionAC
});
function formatRepoAC(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionAC(repo);
    return markup;
}
function formatRepoSelectionAC(repo) {
    return repo.name || repo.text;
}
function wrapperOptionAC(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" +data.name+" "+data.position+ "</div>";
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-envelope'></i> " +data.email+ "</div>";
    markup += "</div></div></div>";
    return markup;
}


$(".sel-ajax-dept").select2({
    ajax: {
        url: '/api/select/dept',
        type: 'post',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                q: params.term,         // search term
                page: params.page,
            };
        },
        processResults: function(res, params) {
            params.page = params.page || 1;
            var data = res.data;
            return {
                results: data.items,
                pagination: {
                    more: (params.page * 20) < data.total_count
                }
            };
        },
        cache: true
    },
    placeholder: 'Search for a repository',
    escapeMarkup: function(markup) {
        return markup;
    }, 
    minimumInputLength: 2,
    templateResult: formatRepoDPT,
    templateSelection: formatRepoSelectionDPT
});
function formatRepoDPT(repo) {
    if (repo.loading) {
        return repo.text;
    }
    var markup = wrapperOptionDPT(repo);
    return markup;
}
function formatRepoSelectionDPT(repo) {
    return repo.name || repo.text;
}
function wrapperOptionDPT(data) {
    var markup = "<div class='select2-result-repository clearfix d-flex'>";
    markup += "<div class='select2-result-repository__meta'>";
    markup += "<div class='select2-result-repository__title fs-lg fw-500'>" +data.name+ "</div>";
    markup += "<div class='select2-result-repository__statistics d-flex fs-sm'>";
    markup += "<div class='select2-result-repository__forks mr-2'><i class='fal fa-hashtag'></i> " +data.alias+">"+data.parent+ "</div>";
    markup += "</div></div></div>";
    return markup;
}
