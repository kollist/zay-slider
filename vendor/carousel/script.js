const carousel = document.querySelector("#carousel"),
firstItem = carousel.querySelectorAll(".item-card")[0],
allItems = carousel.querySelectorAll('.item-card'),
arrowIcons = document.querySelectorAll(".items-wrapper span"),
shortcodeContainer = document.querySelector("#menu-shortcode"),
bulletsContainer = document.querySelector(".items-wrapper .bullets");

let isDragStart = false, isDragging = false, prevPageX, prevScrollLeft, positionDiff, bulletsNbr;


let id = shortcodeContainer.dataset.id;


let itemsOnSlide = parseInt(SLIDER_OPTIONS.slidesToShow[id]);
let showBullets = SLIDER_OPTIONS.showBullets[id];

if (itemsOnSlide > 4){
    document.querySelector('.items-wrapper').style.width = "100%";
    document.querySelector('.items-wrapper').style.marginLeft = 0;
}else {
    document.querySelector('.items-wrapper').style.width = "70%";
    document.querySelector('.items-wrapper').style.marginLeft = "15%";
}


console.log(showBullets)

if (showBullets){
    var activeBulletIndex = 0;
    
    let number = window.innerWidth <= 900 ? allItems.length : allItems.length - itemsOnSlide + 1;

    if (window.innerWidth <= 900){
        bulletsNbr = allItems.length;
    }else {
        bulletsNbr = allItems.length - itemsOnSlide + 1;
    }


    window.addEventListener("resize", function () {
        if (window.innerWidth <= 900){
            bulletsNbr = allItems.length;
        }else {
            bulletsNbr = allItems.length - itemsOnSlide + 1;
        }
    })



    while (bulletsNbr > 0 ){
        let span = document.createElement('span');
        if (bulletsNbr === number){
            span.classList.add('active')
        }
        bulletsContainer.appendChild(span);
        bulletsNbr--;
    }
}

allItems.forEach((item, index) => {
    item.style.width = `calc(100% / ${itemsOnSlide} - 5px)`;
});

const showHideIcons = () => {
    let scrollWidth = carousel.scrollWidth - carousel.clientWidth; 
    arrowIcons[0].style.display = carousel.scrollLeft == 0 ? "none" : "block";
    arrowIcons[1].style.display = carousel.scrollLeft == scrollWidth ? "none" : "block";
}

arrowIcons.forEach(icon => {
    icon.addEventListener("click", () => {
        let firstItemWidth = firstItem.clientWidth + 5; 
        carousel.scrollLeft += icon.id == "left" ? -firstItemWidth : firstItemWidth;
        
        // Update the active bullet index
        let bullets = document.querySelector(".items-wrapper .bullets");
        if (bullets){
            const nextActiveBulletIndex = Math.floor(carousel.scrollLeft / firstItemWidth);
            if (nextActiveBulletIndex !== activeBulletIndex) {
                bullets.children[activeBulletIndex].classList.remove('active');
                bullets.children[nextActiveBulletIndex].classList.add('active');
                activeBulletIndex = nextActiveBulletIndex;
            }
        }
        showHideIcons();
    });
});

const autoSlide = () => {
    if(carousel.scrollLeft - (carousel.scrollWidth - carousel.clientWidth) > -1 || carousel.scrollLeft <= 0) return;

    positionDiff = Math.abs(positionDiff);
    let firstItemWidth = firstItem.clientWidth + 5;
    let valDifference = firstItemWidth - positionDiff;

    let bullets = document.querySelector(".items-wrapper .bullets");
    if (bullets){
        const nextActiveBulletIndex = Math.floor(carousel.scrollLeft / firstItemWidth);
        if (nextActiveBulletIndex !== activeBulletIndex) {
            bullets.children[activeBulletIndex].classList.remove('active');
            bullets.children[nextActiveBulletIndex].classList.add('active');
            activeBulletIndex = nextActiveBulletIndex;
        }
    }

    if(carousel.scrollLeft > prevScrollLeft) {
        return carousel.scrollLeft += positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff;
    }
    carousel.scrollLeft -= positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff;
}

const dragStart = (e) => {
    isDragStart = true;
    prevPageX = e.pageX || e.touches[0].pageX;
    prevScrollLeft = carousel.scrollLeft;
}

const dragging = (e) => {
    if(!isDragStart) return;
    e.preventDefault();
    isDragging = true;
    carousel.classList.add("dragging");
    positionDiff = (e.pageX || e.touches[0].pageX) - prevPageX;
    carousel.scrollLeft = prevScrollLeft - positionDiff;
    showHideIcons();
}

const dragStop = () => {
    isDragStart = false;
    carousel.classList.remove("dragging");

    if(!isDragging) return;
    isDragging = false;
    autoSlide();
}

carousel.addEventListener("mousedown", dragStart);
carousel.addEventListener("touchstart", dragStart);

document.addEventListener("mousemove", dragging);
carousel.addEventListener("touchmove", dragging);

document.addEventListener("mouseup", dragStop);
carousel.addEventListener("touchend", dragStop);