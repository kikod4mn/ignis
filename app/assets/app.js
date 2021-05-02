import './fonts/ProzaLibre-Bold.ttf';
import './fonts/ProzaLibre-BoldItalic.ttf';
import './fonts/ProzaLibre-ExtraBold.ttf';
import './fonts/ProzaLibre-ExtraBoldItalic.ttf';
import './fonts/ProzaLibre-Italic.ttf';
import './fonts/ProzaLibre-Medium.ttf';
import './fonts/ProzaLibre-MediumItalic.ttf';
import './fonts/ProzaLibre-Regular.ttf';
import './fonts/ProzaLibre-SemiBold.ttf';
import './fonts/ProzaLibre-SemiBoldItalic.ttf';
import './fonts/SourceSansPro-Black.ttf';
import './fonts/SourceSansPro-BlackItalic.ttf';
import './fonts/SourceSansPro-Bold.ttf';
import './fonts/SourceSansPro-BoldItalic.ttf';
import './fonts/SourceSansPro-ExtraLight.ttf';
import './fonts/SourceSansPro-ExtraLightItalic.ttf';
import './fonts/SourceSansPro-Italic.ttf';
import './fonts/SourceSansPro-Light.ttf';
import './fonts/SourceSansPro-LightItalic.ttf';
import './fonts/SourceSansPro-Regular.ttf';
import './fonts/SourceSansPro-SemiBold.ttf';
import './fonts/SourceSansPro-SemiBoldItalic.ttf';
import './fonts/SourceCodePro-Black.ttf';
import './fonts/SourceCodePro-BlackItalic.ttf';
import './fonts/SourceCodePro-Bold.ttf';
import './fonts/SourceCodePro-BoldItalic.ttf';
import './fonts/SourceCodePro-ExtraLight.ttf';
import './fonts/SourceCodePro-ExtraLightItalic.ttf';
import './fonts/SourceCodePro-Italic.ttf';
import './fonts/SourceCodePro-Light.ttf';
import './fonts/SourceCodePro-LightItalic.ttf';
import './fonts/SourceCodePro-Regular.ttf';
import './fonts/SourceCodePro-SemiBold.ttf';
import './fonts/SourceCodePro-SemiBoldItalic.ttf';
import './fonts/NotoSerif-Bold.ttf';
import './fonts/NotoSerif-BoldItalic.ttf';
import './fonts/NotoSerif-Italic.ttf';
import './fonts/NotoSerif-Regular.ttf';
import './scss/app.scss';

// import './bootstrap';
//Get the button:
const backToTopBtn = document.getElementById("back-to-top-button");

window.onscroll = function () {scrollFunction()};

function scrollFunction() {
	if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
		backToTopBtn.style.display = "block";
	} else {
		backToTopBtn.style.display = "none";
	}
}