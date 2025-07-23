async function listGalleryMediaFiles(calledAgain = false, page = 0){  
    let pagination = '';

    if(calledAgain && page > 0)
        pagination = `&page=${page}`;

    let res = await makeAjaxCall({
        url: `${webURL}/new-cont-reg?Type=Gall3ry&action=listMedia&home=1${pagination}&ts=${Date.now()}`,
        method: "GET",
        });
    return res; 
}