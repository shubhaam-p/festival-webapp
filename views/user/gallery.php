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
		</div>
	</div>
    <script>
        const webURL = "<?php echo $webURL;?>";
    </script>
    <script src="<?php echo $webURL;?>reckStatic/js/jquery-3.4.1.min.js"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/bootstrap.bundle.min.js"  crossorigin="anonymous"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/wavesurfer.min.js"></script>

	<script src="<?php echo $webURL;?>reckStatic/js/common.js"  crossorigin="anonymous"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/UI-user.js"  crossorigin="anonymous"></script>
	<script>
    $(document).ready(async function () {
		const mediaList = document.getElementById("media-list");
		mediaList.innerHTML = "";
		let result = cls = '';
		let current = 0;
		let startY = 0;
		let isScrolling = false;
		let isPinching = false;

		const AppState = {
			slides: {},
			wavesurfer: []
		};

		let obj = {
			imageDoneValue: 0,
			totalMediaCountValue: NaN,
			letMeKnow() {
				if(obj.imageDone == 1 && !isNaN(obj.totalMediaCount) && obj.totalMediaCount <=0){
					AppState.slides = document.querySelectorAll('.media-slide');
				}
			},
			get imageDone() {
				return this.imageDoneValue;
			},
			set imageDone(value) {
				this.imageDoneValue = value;
				this.letMeKnow();
			},
			get totalMediaCount() {
				return this.totalMediaCountValue;
			},
			set totalMediaCount(value) {
				this.totalMediaCountValue = value;
				this.letMeKnow();
			}
		}

		function showNextSlide(){
			if (current < AppState.slides.length - 1) {
				console.log("inside",AppState.slides[current], AppState.slides[current+1])
				AppState.slides[current].classList.remove('active');
				current++;
				AppState.slides[current].classList.add('active');
			} else {
				// End of media
				console.log("You've reached the end of the exhibition!");
			}
		}

		// Swipe up detection (basic touch event)
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
		await listGalleryMediaFiles().then((result)=>{
			if(result?.total == 0){
				let div = document.createElement("div");
				div.innerHTML = '<div>Packages are currenly unavailable!</div>';
				mediaList.append(div);
			}else{
				let mediaFile = result.data;
				let imageArr = result.imageArr;
				let imageArrData = imageArr.data;
				let imageArrCount = imageArr.total;
				obj.totalMediaCount = result.total;				
				i = j = 0;

				while(imageArrCount > 0){
					let file = file1 = file2 = ''; 
					let cls = '';
					let div = document.createElement("div");
					div.classList.add("media-slide", "col-sm-12", "col-md-6", "col-lg-6", "media-container");
					cls = imageArrData[j].CLASS !='' || imageArrData[j].CLASS != 'undefined'? imageArrData[j].CLASS:'';

					if(j==0)
						div.classList.add("active");

					if(imageArrCount >= 2){
						file1 = `<img src="${imageArrData[j].MEDIA}" class="zoomable ${cls}">`;
						file2 = `<img src="${imageArrData[j+1].MEDIA}" class="zoomable ${cls}">`;
						imageArrCount -=2;
						j +=2;
					}else{
						file1 = `<img src="${imageArrData[j].MEDIA}" class="zoomable ${cls}">`;
						imageArrCount--;
						j++;
					}
					file += file1 + file2;
					mediaList.appendChild(div);
					div.innerHTML = file;
				}

				if(imageArrCount <= 0){
					obj.imageDone=1;
					obj.totalMediaCount -= imageArr.total;

					while(obj.totalMediaCount > 0){
						let isAudio = 0;	
						let div = document.createElement("div");
						div.classList.add("media-slide", "col-sm-12", "col-md-6", "col-lg-6", "media-container");
						if(i==0 && imageArr.total <=0)
							div.classList.add("active");

						cls = mediaFile[i]?.CLASS !='' || mediaFile[i]?.CLASS != 'undefined'? mediaFile[i].CLASS:'';
						let mimeType = mediaFile[i].MIMETYPE.split('/');

						switch(mimeType[0]){
							case 'video':
								file = `<video src="${mediaFile[i].MEDIA}" class="zoomable ${cls}" controls></video>`;
								break;

							case 'audio':
								isAudio = 1;
								// let screenwidth = window.innerWidth;
								file = `
										<div class="audio-wrapper" data-id="${mediaFile[i].ID}">
											<button id="playPause-${mediaFile[i].ID}" class="playPause" data-id="${mediaFile[i].ID}">
												<img src="${webURL}reckStatic/images/play.png" alt="play">
											</button>
											<div class="audio-container" style="width:400px">
												<div id="waveform-${mediaFile[i].ID}" class="waveform" style="display: none;"></div>
											</div>
										</div>`;

								div.innerHTML = file;
								mediaList.appendChild(div);
								createWavesOfAudio(mediaFile[i].MEDIA, mediaFile[i].ID);
								i++;
								obj.totalMediaCount --;

								break;
							default:
								console.log('unspported format');
						}
						if(isAudio != 1){
							// Append images to row
							div.innerHTML = file;
							mediaList.appendChild(div);
							i++;
							obj.totalMediaCount-- ;
						}
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
			AppState.wavesurfer[ID] = WaveSurfer.create(options)

			loadingText = '<p css="loading" style="height:20px; width=119px;">Loading...</p>'
			$('.waveform').css('display','none')
			$(loadingText).insertAfter('.waveform')
			
			AppState.wavesurfer[ID].on('ready',()=>{
				$('#waveform-'+ID+' + p').remove()
				$('#waveform-'+ID).css('display','block')
			})
	
			AppState.wavesurfer[ID].on('finish', () => {
				$('#playPause-'+ID).children()[0].src = `${webURL}reckStatic/images/play.png`
			});
		}

		$('.playPause').click(function(){
			elementId = this.id;
			actualId = parseInt(this.getAttribute('data-id'));

			if(!AppState.wavesurfer[actualId].isPlaying())
				$('#playPause-'+actualId).children()[0].src = `${webURL}reckStatic/images/pause.png`
			else 
				$('#playPause-'+actualId).children()[0].src = `${webURL}reckStatic/images/play.png`

			AppState.wavesurfer[actualId].playPause()
		})
    });
</script>
</body>
</html>