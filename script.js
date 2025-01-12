function win_close(btn) {
	let win = btn.parentNode.parentNode;
	win.remove();
}

function showPostForm(btn) {
	btn.remove();
	let form = document.getElementById("post");
	form.classList.remove("hidden");
}
