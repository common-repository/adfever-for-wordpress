// To close one thickbox and then open another, this is quite handy while you want to open another response modal window on the action of current modal window. 
// Opening more then one thickbox window is not possible on the same page so closing one and opening another could be quite handy and we can change height and width as well for another window.
var jThickboxNewLink;

function tb_remove_open(reloadLink){
	jThickboxReloadLink	=	reloadLink;
	tb_remove();
	setTimeout("jThickboxNewLink();",500);
	return false;
}

// This function will let you open new thickbox window without specifying class="Thickbox" and any href="http://web.com" attribute
// It will be helpful when you are dynamically loading any content and from those content you would like to open Thickbox windows.
// As basically the nature of thickbox is such that it scans all links (<a >..</a>) tags on load using jQuery's $(document).ready function so if your link is loaded using ajex or using any dynamic javascript and was not on page at the time of load then this thickbox setup won't work and you have to use this function.
function tb_open_new(jThickboxNewLink){
	tb_show(null,jThickboxNewLink,null);
}
