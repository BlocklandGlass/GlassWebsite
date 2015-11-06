<?php

//this should be the only class to interact with `addon_files`
class AddonObject {
			id INT AUTO_INCREMENT,
			board INT NOT NULL,
			author INT NOT NULL,
			name VARCHAR(30) NOT NULL,
			filename TEXT NOT NULL,
			description TEXT NOT NULL DEFAULT '',
			file INT NOT NULL,
			deleted TINYINT NOT NULL DEFAULT 0,
			dependencies TEXT NOT NULL DEFAULT '',
			downloads_web INT NOT NULL DEFAULT 0,
			downloads_ingame INT NOT NULL DEFAULT 0,
			downloads_update INT NOT NULL DEFAULT 0,
			updaterInfo TEXT NOT NULL,
			approvalInfo TEXT NOT NULL,
}
?>
