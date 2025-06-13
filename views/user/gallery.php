<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Constants.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gallery</title>
	<link href="<?php echo $webURL;?>reckStatic/css/bootstrap.min.css" rel="stylesheet"  crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo $webURL;?>reckStatic/css/gallery.css">
</head>
<style>
 .media-container {
  width: 100%;
  max-width: 500px;
  margin: auto;
  overflow: hidden;
  border-radius: 10px;
  background-color: #f2f2f2;
}

.media-container img,
.media-container video {
  display: block;
  width: 100%;
  height: auto;
  object-fit: contain;
}

/* Tweak portrait images */
.media-container.portrait img,
.media-container.portrait video {
  max-height: 500px;
  width: auto;
  margin: 0 auto;
}

.playPause{
	height: 45px;
	width: 45px;
	border: none;
}

.audio-wrapper{
	display: flex;
	flex-direction: row;
	align-items: center;
}
</style>
<body>

	<div class="container">
		<div class="row g-4">
            <div id="media-list"></div>
			<!-- <div class="media-slide active col-sm-12 col-md-6 col-lg-6">
				<img src="../UI/images/rock.jpg" class="zoomable">
			  </div>

			<div class="media-slide col-sm-12 col-md-6 col-lg-6">
				<img src="../UI/images/img_5terre.jpg" alt="5 Terre" loading="lazy" class="zoomable" >
			</div>
			  
			<div class="media-slide col-sm-12 col-md-6 col-lg-6">
				<img src="../UI/images/img_mountains.jpg" alt="Norther Lights" loading="lazy" class="zoomable">
			</div>

			<div class="media-slide col-sm-12 col-md-6 col-lg-6">
				<img src="../UI/images/img_forest.jpg" alt="Norther Lights" loading="lazy" class="zoomable">
			</div>

			<div class="media-slide col-sm-12 col-md-6 col-lg-6">
				<img src="../UI/images/paris.jpg" alt="Norther Lights" loading="lazy" class="zoomable">
			</div> -->

		</div>
	</div>
    <script>
        const webURL = "<?php echo $webURL;?>";
    </script>
    <script src="<?php echo $webURL;?>reckStatic/js/jquery-3.4.1.min.js"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/bootstrap.bundle.min.js"  crossorigin="anonymous"></script>
	<script src="https://unpkg.com/wavesurfer.js@7"></script>

	<script src="<?php echo $webURL;?>reckStatic/js/common.js"  crossorigin="anonymous"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/UI-user.js"  crossorigin="anonymous"></script>
	<script>
		// const options = {
		// 	container: document.getElementById('media-list'),
		// 	barWidth: 7,
		// 	waveColor: 'rgb(226, 226, 226)',
		// 	progressColor: 'rgb(46, 33, 223)',
		// 	url: 'http://www.festival-album.com/uploads/20250613_083003_7706.mp3',
		// }
		// const wavesurfer = WaveSurfer.create(options)

		// wavesurfer.on('click', () => {
		// wavesurfer.play()
		// })

		const slides = document.querySelectorAll('.media-slide');
		let current = 0;
	
		function showNextSlide(){
			if (current < slides.length - 1) {
				console.log("inside",slides[current], slides[current+1])
				slides[current].classList.remove('active');
				current++;
				slides[current].classList.add('active');
			  } else {
				// End of media
				// document.getElementById('nextBtn').disabled = true;
				console.log("You've reached the end of the exhibition!");
			  }
		}

		// Swipe up detection (basic touch event)
		let startY = 0;
		let isScrolling = false;
		let isPinching = false;

		window.addEventListener('touchstart', (e) => {
			if (e.touches.length > 1) {
				isPinching = true; // Pinch gesture
			} else {
				isPinching = false;
				startY = e.touches[0].clientY;
			}
		});

		window.addEventListener('touchend', (e) => {
			if(isScrolling || isPinching) return;

			const endY = e.changedTouches[0].clientY;
			const deltaY = startY - endY;

			if (deltaY > 50) {
				showNextSlide();
				console.log("touchend - swipe")
				setTimeout(()=>{
					isScrolling = false;
				}, 800);
			}
		});

		// Scroll down detection for non-touch devices
		window.addEventListener('wheel', (e) => {
			if(isScrolling) return;
			if (e.deltaY > 20) {
				isScrolling = true;
				console.log("wheel - swipe")
				showNextSlide();	
			}
			setTimeout(()=>{
				isScrolling = false;
			}, 800);
			
			}, { passive: true }
		);

		//Zoom
		const images = document.querySelectorAll('.zoomable');
		images.forEach(img => {
			img.addEventListener('dblclick', (e) => {
				const isZoomed = img.classList.contains('img-zoom');
				// Remove zoom from all images
				images.forEach(i => i.classList.remove('img-zoom'));

				if(!isZoomed){
					const rect = img.getBoundingClientRect();
					const offsetX = e.clientX - rect.left;
					const offsetY = e.clientY - rect.top;
		
					img.style.transformOrigin = `${offsetX}px ${offsetY}px`;
					img.classList.toggle('img-zoom');
				}
			});
		});

        //Fetch images
        $(document).ready(async function () {
			var wavesurfer = [];

            const mediaList = document.getElementById("media-list");
            mediaList.innerHTML = "";
            let result = '';
            await listGalleryMediaFiles().then((result)=>{
				// result.total = 0;
				if(result?.total == 0){
					let div = document.createElement("div");
					div.innerHTML = '<div>Packages are currenly unavailable!</div>';
					mediaList.append(div);
				}else{
					let mediaFile = result.data; 
					for(let i = 0; i < result.total; i++){
						let isAudio = 0;	
						let div = document.createElement("div");
						div.classList.add("media-slide", "active", "col-sm-12", "col-md-6", "col-lg-6", "media-container"); // Bootstrap row
						// console.log('mffd ' ,mediaFile[1]);
						let cls = mediaFile[i].CLASS !='' || mediaFile[i].CLASS != 'undefined'? mediaFile[i].CLASS:'';
						let mimeType = mediaFile[i].MIMETYPE.split('/');
						let file = ''; 
						switch(mimeType[0]){
							case 'image':
								file = `<img src="${mediaFile[i].MEDIA}" class="zoomable ${cls}">`;
								
								break;
							case 'video':
								file = `<video src="${mediaFile[i].MEDIA}" class="zoomable ${cls}" controls></video>`;
								break;
							case 'audio':
								isAudio = 1;
								let screenwidth = window.innerWidth;
								// file = `<audio src="${mediaFile[i].MEDIA}" class="zoomable ${cls}" controls></audio>`;
								file = `
											<div class="audio-wrapper" data-id="${mediaFile[i].ID}">
												<button id="playPause-${mediaFile[i].ID}" class="playPause" data-id="${mediaFile[i].ID}">
													<img src="${webURL}reckStatic/images/play.png" alt="play">
												</button>
												<div class="audio-container" style="width:400px">
													<div id="waveform-${mediaFile[i].ID}" class="waveform" style="display: none;"></div>
												</div>
											</div>
								`
								div.innerHTML = file;
								mediaList.appendChild(div);
								createWavesOfAudio(mediaFile[i].MEDIA, mediaFile[i].ID);

								break;
							default:
								console.log('unspported format');
						}

						if(isAudio != 1){
							// Append images to row
							div.innerHTML = file;
							mediaList.appendChild(div);
						}
					}
				}
            });

			function createWavesOfAudio(audioURL, ID = 0){
				ID = parseInt(ID);
				const options = {
				   container: 'id',
				   waveColor: '#ababab',
				   progressColor: '#0025d1',
				   cursorColor:'#ddd5e9',
				   cursorWidth: 2,
				   height:200,
				   interact: true,
				   barWidth: 3,
				   barGap: 1,
				   barRadius: 2,
				   url: "URL",    
			   }
	
				options.url= audioURL
				options.container='#waveform-'+ID;
				wavesurfer[ID] = WaveSurfer.create(options)
	
				loadingText = '<p css="loading" style="height:20px; width=119px;">Loading...</p>'
				$('.waveform').css('display','none')
				$(loadingText).insertAfter('.waveform')
				
				wavesurfer[ID].on('ready',()=>{
					$('#waveform-'+ID+' + p').remove()
					$('#waveform-'+ID).css('display','block')
				})
		
				wavesurfer[ID].on('finish', () => {
					$('#playPause-'+ID).children()[0].src = `${webURL}reckStatic/images/play.png`
				});
			}
	
			 $('.playPause').click(function(){
				elementId = this.id;
				console.log(this);
				actualId = parseInt(this.getAttribute('data-id'));
				console.log(elementId, actualId);

				if(!wavesurfer[actualId].isPlaying())
					$('#playPause-'+actualId).children()[0].src = `${webURL}reckStatic/images/pause.png`
				else 
					$('#playPause-'+actualId).children()[0].src = `${webURL}reckStatic/images/play.png`

				// $('#playPause'+playerNo).children()[0].src = `${CONST_RECKSRC}${CONST_THEME_D}web/img/ph_play-fill.png`
	
				wavesurfer[actualId].playPause()
			})
        });


  
	  </script>
</body>
</html>
<!-- {"status":"success","message":"Fetched successfully!","data":[{"ID":12,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_203905_5000.png","HEIGHT":340,"WIDTH":587,"MIMETYPE":"image\/png"},{"ID":11,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_203703_5936.png","HEIGHT":508,"WIDTH":402,"MIMETYPE":"image\/png"},{"ID":10,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_203614_6665.mp4","HEIGHT":720,"WIDTH":1280,"MIMETYPE":"video\/mp4"},{"ID":9,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_203457_6868.mp4","HEIGHT":0,"WIDTH":0,"MIMETYPE":"video\/mp4"},{"ID":8,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_203123_9737.png","HEIGHT":0,"WIDTH":0,"MIMETYPE":"image\/png"},{"ID":7,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_202658_6444.png","HEIGHT":0,"WIDTH":0,"MIMETYPE":"image\/png"},{"ID":6,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_202315_6686.png","HEIGHT":0,"WIDTH":0,"MIMETYPE":"image\/png"},{"ID":5,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_201913_5633.png","HEIGHT":0,"WIDTH":0,"MIMETYPE":"image\/png"},{"ID":4,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250609_000818_9690.mp4","HEIGHT":0,"WIDTH":0,"MIMETYPE":"video\/mp4"},{"ID":3,"IMAGE":"http:\/\/www.festival-album.com\/uploads\/20250608_193655_1462.mp4","HEIGHT":0,"WIDTH":0,"MIMETYPE":"video\/mp4"}]} -->