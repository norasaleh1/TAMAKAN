setTimeout(() => {
  splash.classList.add("fade-out");
  setTimeout(() => {
    splash.style.display = "none";
  }, 800);
}, 2000); // يبقى ظاهر ثانيتين قبل يختفي
setTimeout(() => { splash.style.display = 'none'; }, 600);
