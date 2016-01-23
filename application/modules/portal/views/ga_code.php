{{> templates/header}}

<div id="step1">
	<h3>Step 1</h3>
	<p>
		Please enter the URL of one of the articles/blog posts to get personalized suggestions:<br />
		<input type="text" class="url" value="http://www.boredombase.com/culture/the-story-behind-this-photo-of-an-iceberg-will-shock-you/" style="width:500px;" />
	</p>

	<input type="button" value="Continue ..." onclick="step2();" />
</div>

<div id="step2" style="display:none;">
	<h3>Step 2</h3>

	<p>
		<b>Google Analytics version</b><br />
		Please select the version you are using:<br />
		<input class="question0" name="question0" type="radio" value="1"> Universal Analytics (analytics.js)<br />
		<input class="question0" name="question0" type="radio" value="2"> Classic Google Analytics (ga.js)<br />
	</p>

	<p>
		<b>Author TAG</b><br />
		Please enter the class name associated with Author tag:<br />
		<input type="text" class="question1" value="" /><br />
		<i>From your page: </i><span class="question1_sample"></span>
	</p>

	<p>
		<b>Page URL</b><br />
		Please enter the way you would like to pull the URL:<br />
		<input class="question2" name="question2" type="radio" value="1"> Meta tage OG:URL<br />
		<input class="question2" name="question2" type="radio" value="2"> Meta tage REL Canonical<br />
		<input class="question2" name="question2" type="radio" value="3"> Javascript document.location<br />
		<i>From your page: </i><span class="question2_sample"></span>
	</p>

	<textarea id="code" readonly="readonly" style="width:800px;height:200px;"></textarea>
</div>

<script type="text/javascript">
	var base1 = "<" + "script type='text/javascript'>\r\n  ga('send', 'event', 'Author', !question1!, !question2!);\r\n</" + "script>";
	var base2 = "<" + "script type='text/javascript'>\r\n  _gaq.push(['_trackEvent', 'Author', !question1!, !question2!]);\r\n</" + "script>";

	function step2() {
		if ($('.url').val() != "") {
			$('#step1').spin();
			$.getJSON('/ajax/get_url_suggestions', { url: $('.url').val() }, function (data) {
				$('#step1').hide();
				$('#step2').show();

				if (data.ga != undefined) {
					$(".question0[value='" + data.ga + "']").prop("checked", true);
				}

				if (data.author_class != undefined) {
					$(".question1").val(data.author_class);
					$(".question1_sample").text(data.author_text);
				}
				else
					$(".question1_sample").text("N/A");

				if (data.url_option != undefined) {
					$(".question2[value='" + data.url_option + "']").prop("checked", true);
					$(".question2_sample").text(data.url_text);
				}
				else
					$(".question1_sample").text("N/A");

				update();
			}).always(function () {
				$('#step1').spin(false);
			});;
		}
	}

	function update() {
		var question1 = "(function(x) { return x.nodeName.toLowerCase() === \"meta\" ? x.content : x.textContent || x.innerText })(document.querySelector(\"!author!\"))";
		question1 = question1.replace('!author!', ("." + $("input.question1").val().replace("  ", " ").replace(" ", ".")).replace("..", "."));

		var question2 = "document.querySelector(\"meta[property='og:url']\").content";
		switch ($("input.question2[type='radio']:checked").val()) {
			case "2":
				question2 = "document.querySelector(\"link[rel='canonical']\").href";
				break;
			case "3":
				question2 = "document.location.href";
				break;
		}

		switch($("input.question0[type='radio']:checked").val())
		{
			case "1":
				$("#code").val(base1.replace('!question1!', question1).replace('!question2!', question2));
				break;
			case "2":
				$("#code").val(base2.replace('!question1!', question1).replace('!question2!', question2));
				break;
			default:
				$("#code").val("Please selected options above.");
				break;
		}
	}
	$(function () {
		update();
	});
	$("input.question0, input.question1, input.question2").on("change", update);
</script>

{{> templates/footer}}
