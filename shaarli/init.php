<?php
class Shaarli extends Plugin {
	private $link;
	private $host;

	function about() {
		return array("2.0.0",
			"Shaare your links ! (Sebsauvage Shaarli : http://sebsauvage.net/wiki/doku.php?id=php:shaarli )",
			"jc.saaddupuy, joshu@unfettered.net, GTT");
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
		$value = $this->host->get($this, "shaarli");
		?>
		<div dojoType="dijit.layout.AccordionPane" title="<?= __("Shaarli") ?>">
			<br/>
			<form dojoType="dijit.form.Form">

				<?= \Controls\pluginhandler_tags($this, "save") ?>
				<script type="dojo/method" event="onSubmit" args="evt">
					evt.preventDefault();
					if (this.validate()) {
						Notify.progress('Saving Shaarli configuration...', true);
						xhr.post("backend.php", this.getValues(), (reply) => {
							Notify.info(reply);
						})
					}
				</script>

				<table width="100%" class="prefPrefsList">
					<tr>
						<td width="40%"><?= __("Shaarli url") ?></td>
						<td class="prefValue"><input dojoType="dijit.form.ValidationTextBox" required="1" name="shaarli_url" regExp='^(http|https)://.*' value="<?= $value ?>"></td>
					</tr>
				</table>
				<?= \Controls\submit_tag(__("Save")) ?>
			</form>
		</div>
		<?php
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
