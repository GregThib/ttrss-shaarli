<?php
require_once "config.php";

class Shaarli extends Plugin {
	private $link;
	private $host;

	function about() {
		return array("1.8.0",
		  "Shaare your links ! (Sebsauvage Shaarli : http://sebsauvage.net/wiki/doku.php?id=php:shaarli )",
		  "jc.saaddupuy, joshu@unfettered.net");
	}

	function init($host) {
		$this->host = $host;
		$this->pdo = Db::pdo();

		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
		$host->add_hook($host::HOOK_PREFS_TAB, $this);
	}

	function save() {
		$shaarli_url = ($_POST["shaarli_url"]);
		$this->host->set($this, "shaarli", $shaarli_url);
		echo "Value set to $shaarli_url";
	}

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/shaarli.js");
	}

	function hook_prefs_tab($args) {
		if ($args != "prefPrefs") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("Shaarli")."\">";

		print "<br/>";

		$value = $this->host->get($this, "shaarli");
		print "<form dojoType=\"dijit.form.Form\">";

		print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
		  evt.preventDefault();
		if (this.validate()) {
		  console.log(dojo.objectToQuery(this.getValues()));
		  new Ajax.Request('backend.php', {
			parameters: dojo.objectToQuery(this.getValues()),
			  onComplete: function(transport) {
				notify_info(transport.responseText);
		}
		});
		}
			   </script>";

		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"op\" value=\"pluginhandler\">";
		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"method\" value=\"save\">";
		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"plugin\" value=\"shaarli\">";
		print "<table width=\"100%\" class=\"prefPrefsList\">";
		print "<tr><td width=\"40%\">".__("Shaarli url")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"shaarli_url\" regExp='^(http|https)://.*' value=\"$value\"></td></tr>";
		print "</table>";
		print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

		print "</form>";

		print "</div>"; #pane

	}

	function hook_article_button($line) {
		return "<img src=\"plugins.local/shaarli/shaarli.png\"
				 style=\"cursor : pointer\" style=\"cursor : pointer\"
				 onclick=\"shaarli(".$line["id"].")\"
				 class='tagsPic' title='".__('Bookmark on Shaarli')."'>";
	}

	function getShaarli() {

		$id = $_REQUEST['id'];
		$sth = $this->pdo->prepare("SELECT title, link 
									FROM ttrss_entries, ttrss_user_entries 
									WHERE id = ? AND ref_id = id  AND owner_uid = ?");
		$sth->execute([$id, $_SESSION['uid']]);

		if ($row = $sth->fetch()) {
			$title = truncate_string(strip_tags($row['title']), 100, '...');
			$article_link = $row['link'];
		}

		$shaarli_url = $this->host->get($this, "shaarli");

		print json_encode(array("title" => $title, 
								"link" => $article_link,
				                "id" => $id, 
								"shaarli_url" => $shaarli_url));
	}

	function api_version() {
		 return 2;
	  }
}
?>
