function makeGradientColor(color1, color2, percent) {
    var newColor = {};

    function makeChannel(a, b) {
        return(a + Math.round((b-a)*(percent/100)));
    }

    function makeColorPiece(num) {
        num = Math.min(num, 255);   // not more than 255
        num = Math.max(num, 0);     // not less than 0
        var str = num.toString(16);
        if (str.length < 2) {
            str = "0" + str;
        }
        return(str);
    }

    newColor.r = makeChannel(color1.r, color2.r);
    newColor.g = makeChannel(color1.g, color2.g);
    newColor.b = makeChannel(color1.b, color2.b);
    newColor.cssColor = "#" + 
                        makeColorPiece(newColor.r) + 
                        makeColorPiece(newColor.g) + 
                        makeColorPiece(newColor.b);
    return(newColor);
}
// $('span[data-perc]').css('color', makeGradientColor('red', 'green', $(this).data('perc')));

el = document.querySelectorAll('span[data-perc]'); 
for(i in el){
    color = makeGradientColor({r:255, g:0, b:0}, {r:0, g:255, b:0}, parseInt(el[i].dataset['perc'])).cssColor
    el[i].style.color = color;
}

