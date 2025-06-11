async function listGalleryMediaFiles(pckId = '', PackDetails = false){  
    let getMedia = '';
    let res = await makeAjaxCall({
        url: `${webURL}/new-cont-reg?Type=Gall3ry&action=listMedia&home=true`,
        method: "GET",
        });
    return res; 
}