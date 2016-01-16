{{> templates/header}}

<h3>1. Author TAG</h3>
<p>
	Please enter the class name associated with Author tag:<br />
	<input type="text" class="question1" value="author-name author" />
</p>

<h3>2. Page URL</h3>
<p>
	Please enter the way you would like to pull the URL:<br />
	<input class="question2" name="question2" type="radio" value="1" checked="checked"> Meta tage OG:URL<br />
	<input class="question2" name="question2" type="radio" value="2"> Meta tage REL Canonical<br />
	<input class="question2" name="question2" type="radio" value="3"> Javascript document.location<br />
</p>

<textarea id="code" readonly="readonly" style="width:800px;height:200px;"></textarea>

<script type="text/javascript">
var base = "<"+"script type='text/javascript'>\r\n  ga('send', 'event', 'Author', !question1!, !question2!);\r\n</"+"script>";

function update() {
	var question1 = "(function(x) { return x.textContent || x.innerText; })(document.querySelector(\"!author!\"))";
	question1 = question1.replace('!author!', "." + $("input.question1").val().replace("  ", " ").replace(" ", "."));

	var question2 = "document.querySelector(\"meta[property='og:url']\").content";
	switch($("input.question2[type='radio']:checked").val())
	{
		case "2":
			question2 = "document.querySelector(\"link[rel='canonical']\").href";
			break;
		case "3":
			question2 = "document.location.href";
			break;
	}

	$("#code").val(base.replace('!question1!', question1).replace('!question2!', question2));
}
$(function() {
	update();
});
$("input.question1, input.question2").on("change", update);

</script>

{{> templates/footer}}
