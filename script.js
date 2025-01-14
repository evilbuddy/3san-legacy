function win_close(btn) {
	let win = btn.parentNode.parentNode;
	win.remove();
}

function win_hide(btn) {
	let win = btn.parentNode.parentNode;
	win.classList.add("hidden");
}

function showPostForm(btn) {
	btn.remove();
	let form = document.getElementById("post");
	form.classList.remove("hidden");
}

function showReplyForm() {
	let form = document.getElementById("reply");
	form.classList.remove("hidden");
}

function addReply(id) {
	let textarea = document.getElementById("post").getElementsByTagName("textarea")[0];

	if(textarea.value.trim() != "") {
		textarea.value += "\n";
	}

	textarea.value += ">>" + id + "\n";
	showReplyForm();
}

document.addEventListener("DOMContentLoaded", () => {
	let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
	let form = document.getElementById("reply");
	
	if(form == undefined) { return }

	form.getElementsByClassName("title")[0].onmousedown = (e) => {
		e = e || window.event;
		e.preventDefault();
		pos3 = e.clientX;
		pos4 = e.clientY;
		document.onmouseup = () => {
			document.onmouseup = null;
			document.onmousemove = null;
		};
		document.onmousemove = (e) => {
			e = e || window.event;
			e.preventDefault();

			pos1 = pos3 - e.clientX;
			pos2 = pos4 - e.clientY;
			pos3 = e.clientX;
			pos4 = e.clientY;

			form.style.top = (form.offsetTop - pos2) + "px";
			form.style.left = (form.offsetLeft - pos1) + "px";
		};
	}
});
