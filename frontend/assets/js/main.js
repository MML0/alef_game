// loader.js
(function (global) {
  const DEFAULT_ID = "appLoader";

  function getEl(id = DEFAULT_ID) {
    const el = document.getElementById(id);
    if (!el) throw new Error(`Loader element not found. Expected id="${id}"`);
    return el;
  }

  function show({ id = DEFAULT_ID } = {}) {
    const el = getEl(id);
    el.classList.add("is-active");
    el.setAttribute("aria-hidden", "false");
  }

  // Fades out, then disables interactions (via CSS class removal)
  function hide({ id = DEFAULT_ID, fadeMs = 280 } = {}) {
    const el = getEl(id);

    // if already hidden, do nothing
    if (!el.classList.contains("is-active")) return;

    el.classList.remove("is-active");
    el.setAttribute("aria-hidden", "true");

    // In case you want to fully remove it from DOM after fade:
    // setTimeout(() => el.remove(), fadeMs);
  }

  global.Loader = { show, hide };
})(window);
function showLegacyImage() {
  const legacyImage = document.querySelector('.legacy-img');
  
  // Show legacy image with fade-in effect
  setTimeout(() => {
    legacyImage.style.opacity = 1;
  }, 200); 

  // Hide legacy image after 2 seconds
  setTimeout(() => {
    legacyImage.style.opacity = 0;
  }, 2000); 
}
function showPersonImages() {
  const personImages = document.querySelectorAll('.person-img');
  
  // Show person images one by one with delays
  personImages.forEach((img, index) => {
    setTimeout(() => {
      img.style.opacity = 1;
    //   img.classList.add('animate-wipe');  
    }, 1000 * (index + 1));  // Delay for each person
  });

  // After all images are shown, wipe person images one by one
  setTimeout(() => {
    personImages.forEach((img, index) => {
      setTimeout(() => {
        img.style.opacity = 0; // Fade out images
      }, 1000 * (index + 1));
    });
  }, 7000000);  // Delay the wipe to let all images show
}

setTimeout(Loader.show, 100); // Show the loader after 1 second




document.addEventListener("DOMContentLoaded", function () {
  // Show loader, then hide it after 3 seconds
  setTimeout(Loader.hide, 500); // Hide the loader after 4 seconds

  // After the loader hides, show the story section with animations
  setTimeout(showStorySection, 1500);  // Adjust to fit loader hide time

function showStorySection() {
  const story = document.querySelector('.story');
  story.style.display = 'flex'; // Show the story section
  
  // Call the functions to handle legacy image and person images
  showLegacyImage();
  showPersonImages();
}

});
