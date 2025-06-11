async function makeAjaxCall({url, method = "GET", data}) {
    return new Promise((resolve, reject) => {
        const isFormData = data instanceof FormData;
        const ajaxOptions = {
            url: url,
            method: method,
            data: data,
            processData: !isFormData,
            contentType: isFormData ? false : "application/x-www-form-urlencoded; charset=UTF-8",
            success: function(data) {
                resolve(JSON.parse(data));
            },
            error: function(error) {
                reject(error);
            },
        };
        $.ajax(ajaxOptions).fail(function(error) {
            reject(error);
        });
    });
}