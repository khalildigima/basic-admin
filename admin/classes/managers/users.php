<?php
require_once(__DIR__ . "/base_table.php");

class Users extends BaseTable
{
    public function __construct()
    {
        parent::__construct("users");
    }

    // user type
    public function get_user_type($user_type_id)
    {
        $userTypeModel = new UserTypes();
        return $userTypeModel->get_by("id", $user_type_id);
    }

    public function get_user_type_name($user_type_id)
    {
        $usertype = $this->get_user_type($user_type_id);
        if (!$usertype) {
            return null;
        }

        return $usertype["name"];
    }

    // drug license
    public function get_drug_license($user_id)
    {
        $model = new DrugLicenses();
        $row = $model->get_by("user_id", $user_id);
        if (!$row) return null;

        return $row;
    }

    public function have_drug_license($user_id)
    {
        $model = new Users();
        $row = $model->get_by_id($user_id);
        if (!$row) return false;

        if ($row["type_id"] != RETAILER) return false;

        $row = $this->get_drug_license($user_id);
        if (!$row) return false;
        if ($row["drug_license_file"] == "") return false;

        return true;
    }

    public function have_perm($user_id, $perm_id)
    {
        $model = new UserPermissions();
        return $model->have_perm($user_id, $perm_id);
    }

    public function can_access($perm_id)
    {
        if ($_SESSION["user_type_id"] == ADMIN) return true;
        $model = new UserPermissions();
        return $model->have_perm($_SESSION["user_id"], $perm_id);
    }

    public function generate_uid($user_id)
    {
        return "VT_" . $user_id;
    }

    public function get_screen_age($user_id)
    {
        $user = $this->get_by_id($user_id);
        if (!$user) return null;

        return $user["screen_age"];
        /**
		$currentYear = date('Y');
		$birthYear = $this->format_date($user["birth_date"], "Y");
		$real_age = $currentYear - $birthYear;
		return $real_age + $user["screen_age"];
		/**/
    }


    public function get_registered_as($user_id)
    {
        $sql = "SELECT actor_types.name FROM actor_types 
                JOIN user_actor_type ON user_actor_type.actor_type_id = actor_types.id
                WHERE user_actor_type.user_id = :user_id";

        $conn = $this->get_con();
        if (!$conn) {
            return $this->set_last_error("Connection does not exists");
        }

        $user_id = $this->clean_string($user_id);

        $stmt = $conn->prepare($sql);
        $stmt->bindparam(":user_id", $user_id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();

        return $data;
    }

    public function get_interest_names($user_id)
    {
        $sql = "SELECT interest_fields.name FROM interest_fields 
                JOIN user_interest ON user_interest.interest_id = interest_fields.id
                WHERE user_interest.user_id = :user_id";

        $conn = $this->get_con();
        if (!$conn) {
            return $this->set_last_error("Connection does not exists");
        }

        $user_id = $this->clean_string($user_id);

        $stmt = $conn->prepare($sql);
        $stmt->bindparam(":user_id", $user_id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();

        return $data;
    }


    public function ajax_filter(

        $male = false,   // gender
        $female = false, // gender
        $other = false,  // gender

        $country = false,
        $state = false,
        $city = false,

        $hair = false,
        $body_type = false,

        $experience = false,

        $driver_license = false,
        $passport = false,

        $min = false, // age
        $max = false,  // age 
        $start_index = 0
    ) {
        if ($start_index < 0) $start_index = 0;
        $sql = "SELECT  users.id, users.first_name, users.middle_name, 
						users.last_name, users.screen_age, locations.country, locations.state, locations.city, users.internal_rank, users.is_approved FROM `users`
				JOIN locations ON locations.user_id = users.id
				JOIN appearance ON appearance.user_id = users.id
				JOIN work ON work.user_id = users.id
				JOIN noting ON noting.user_id = users.id
				WHERE 
				users.type_id = 3";

        $gender = "";
        /**/
        if ($male || $female || $other) {
            $gender = " AND (users.gender = ''";
        }
        if ($male) $gender .= " OR users.gender = 'male'";
        if ($female) $gender .= " OR users.gender = 'female'";
        if ($other) $gender .= " OR users.gender = 'other'";

        if ($male || $female || $other) {
            $gender .= ")";
        }
        /**/

        $loc = "";
        if ($country) {
            $loc = " AND locations.country='$country' ";
        }

        $state_ = "";
        if ($state && $state != "None") $state_ = " AND locations.state = '" . $this->clean_string($state) . "'";

        $city_ = "";
        if ($city && $city != "No cities found" && $city != "None" && $city != "Please wait ...") {
            $city_ = " AND locations.city = '" . $this->clean_string($city) . "'";
        }

        $ahair = "";
        if ($hair) {
            $ahair = " AND appearance.hair_length='$hair' ";
        }

        $abody = "";
        if ($body_type) {
            $ahair = " AND appearance.body_type='$body_type' ";
        }

        $exp = "";
        if ($experience) {
            $exp = " AND work.experience=$experience ";
        }

        $drive = "";
        if ($driver_license) {
            $drive = " AND noting.driver_license=1";
        }

        $passp = "";
        if ($passport) {
            $passp = " AND noting.passport=1 ";
        }

        $minage = "";
        if ($min) {
            // $minage = " AND ((year(CURDATE()) - year(users.birth_date)) + 0) >= $min " ;
            $minage = " AND screen_age >= $min ";
        }

        $maxage = "";
        if ($max) {
            // $maxage = " AND ((year(CURDATE()) - year(users.birth_date)) + 0) <= $max " ;
            $maxage = " AND screen_age <= $max ";
        }

        $sql .= $gender;
        $sql .= $loc;
        $sql .= $state_;
        $sql .= $city_;
        $sql .= $ahair;
        $sql .= $abody;
        $sql .= $exp;
        $sql .= $drive;
        $sql .= $passp;
        $sql .= $minage;
        $sql .= $maxage;

        $show_profile_per_page = 12;
        $sql .= " LIMIT " . $show_profile_per_page . " OFFSET " . ($start_index * $show_profile_per_page);

        $conn = $this->get_con();
        if (!$conn) {
            return $this->set_last_error("Connection does not exists");
        }
        $male = $this->clean_string($male);
        $female = $this->clean_string($female);
        $country = $this->clean_string($country);
        $hair = $this->clean_string($hair);
        $body_type = $this->clean_string($body_type);
        $experience = $this->clean_string($experience);
        $driver_license = $this->clean_string($driver_license);
        $passport = $this->clean_string($passport);


        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();

        return $data;
    }

    function advance_search()
    {
        $sql = "
		SELECT DISTINCT users.id FROM `users`
		JOIN locations ON locations.user_id = users.id
		JOIN appearance ON appearance.user_id = users.id
		JOIN work ON work.user_id = users.id
		JOIN talents ON talents.user_id = users.id
		JOIN noting ON noting.user_id = users.id
		JOIN media ON media.user_id = users.id
		JOIN user_about ON user_about.user_id = users.id
		JOIN user_actor_type ON user_actor_type.user_id = users.id
		JOIN user_interest ON user_interest.user_id = users.id
		WHERE 
		type_id = 3
		";

        $gender = "";
        if ($_POST["gender"] == "male") $gender = " AND users.gender = 'male'";
        if ($_POST["gender"] == "female") $gender = " AND users.gender = 'female'";
        if ($_POST["gender"] == "other") $gender = " AND users.gender = 'other'";


        $minage = "";
        if ($_POST["min_age"] != "") {
            // $minage = " AND ((year(CURDATE()) - year(users.birth_date)) + 0) >= " . $this->clean_string($_POST["min_age"]);
            $minage = " AND screen_age >= " . $this->clean_string($_POST["min_age"]);
        }

        $maxage = "";
        if ($_POST["max_age"] != "") {
            // $maxage = " AND ((year(CURDATE()) - year(users.birth_date)) + 0) <= " . $this->clean_string($_POST["max_age"]);
            $maxage = " AND screen_age <= " . $this->clean_string($_POST["max_age"]);
        }

        $below_one_year = "";
        if (isset($_POST["below_one_year"])) {
            $below_one_year = " AND users.below_one_year = 1";
            $minage = "";
            $maxage = "";
        }

        // -- location 
        $country = "";
        if ($_POST["country"] != "None") $country = " AND locations.country = '" . $this->clean_string($_POST["country"]) . "'";

        $state = "";
        if ($_POST["state"] != "None") $state = " AND locations.state = '" . $this->clean_string($_POST["state"]) . "'";

        $city = "";
        if ($_POST["city"] != "No cities found" && $_POST["city"] != "None" && $_POST["city"] != "Please wait ...") {
            $city = " AND locations.city = '" . $this->clean_string($_POST["city"]) . "'";
        }

        $langauge = "";
        if ($_POST["langauge"] != "") $langauge = " AND locations.langauge like '%" . $this->clean_string($_POST["langauge"]) . "%'";

        $other_languages = "";
        if ($_POST["other_languages"] != "") $other_languages = " AND locations.other_languages like '%" . $this->clean_string($_POST["other_languages"]) . "%'";

        $ethnicity = "";
        if ($_POST["ethnicity"] != "") $ethnicity = " AND locations.ethnicity like '%" . $this->clean_string($_POST["ethnicity"]) . "%'";

        // -- appearance 
        $minheight = "";
        if ($_POST["min_height"] != "") {
            $minheight = " AND appearance.min_height  >= " . $this->clean_string($_POST["min_height"]);
        }

        $maxheight = "";
        if ($_POST["max_height"] != "") {
            $maxheight = " AND appearance.max_height  <= " . $this->clean_string($_POST["max_height"]);
        }

        $hair_length = "";
        if ($_POST["hair_length"] != "None") $hair_length = " AND appearance.hair_length = '" . $this->clean_string($_POST["hair_length"]) . "'";

        $hair_color = "";
        if ($_POST["hair_color"] != "None") $hair_color = " AND appearance.hair_color = '" . $this->clean_string($_POST["hair_color"]) . "'";

        $skin_color = "";
        if ($_POST["skin_color"] != "None") $skin_color = " AND appearance.skin_color = '" . $this->clean_string($_POST["skin_color"]) . "'";

        $eye_color = "";
        if ($_POST["eye_color"] != "None") $eye_color = " AND appearance.eye_color = '" . $this->clean_string($_POST["eye_color"]) . "'";

        $body_type = "";
        if ($_POST["body_type"] != "None") $body_type = " AND appearance.body_type = '" . $this->clean_string($_POST["body_type"]) . "'";

        // -- work
        $experience = "";
        if ($_POST["experience"] != "None") $experience = " AND work.experience = '" . $this->clean_string($_POST["experience"]) . "'";

        $theatre_experience = "";
        if (isset($_POST["theatre_experience"])) $theatre_experience = " AND work.theatre_experience = 1";

        $bold_scenes = "";
        if (isset($_POST["bold_scenes"])) $bold_scenes = " AND work.bold_scenes = 1";

        $south_movies = "";
        if (isset($_POST["south_movies"])) $south_movies = " AND work.south_movies = 1";

        // -- talents 
        $formal_training = "";
        if (isset($_POST["formal_training"])) $formal_training = " AND talents.formal_training = 1";

        $music_instruments = "";
        if ($_POST["music_instruments"] != "") $music_instruments = " AND talents.music_instruments like '%" . $this->clean_string($_POST["music_instruments"]) . "%'";

        $dance_styles = "";
        if ($_POST["dance_styles"] != "") $dance_styles = " AND talents.dance_styles like '%" . $this->clean_string($_POST["dance_styles"]) . "%'";

        $sports = "";
        if ($_POST["sports"] != "") $sports = " AND talents.sports like '%" . $this->clean_string($_POST["sports"]) . "%'";

        $special_skills = "";
        if ($_POST["special_skills"] != "") $special_skills = " AND talents.special_skills like '%" . $this->clean_string($_POST["special_skills"]) . "%'";


        $hobbies = "";
        if ($_POST["hobbies"] != "") $hobbies = " AND talents.hobbies like '%" . $this->clean_string($_POST["hobbies"]) . "%'";

        //-- noting --//
        $braces = "";
        if (isset($_POST["braces"])) $braces = " AND noting.braces = 1";

        $glasses = "";
        if (isset($_POST["glasses"])) $glasses = " AND noting.glasses = 1";

        $hearing_impared = "";
        if (isset($_POST["hearing_impared"])) $hearing_impared = " AND noting.hearing_impared = 1";

        $handicap = "";
        if (isset($_POST["handicap"])) $handicap = " AND noting.handicap = 1";

        $tattoo = "";
        if (isset($_POST["tattoo"])) $tattoo = " AND noting.tattoo = 1";

        $ride_bike = "";
        if (isset($_POST["ride_bike"])) $ride_bike = " AND noting.ride_bike = 1";

        $ride_car = "";
        if (isset($_POST["ride_car"])) $ride_car = " AND noting.ride_car = 1";

        $driver_license = "";
        if (isset($_POST["driver_license"])) $driver_license = " AND noting.driver_license = 1";

        $passport = "";
        if (isset($_POST["passport"])) $passport = " AND noting.passport = 1";

        $union_card = "";
        if (isset($_POST["union_card"])) $union_card = " AND noting.union_card = 1";

        $twin = "";
        if (isset($_POST["twin"])) $twin = " AND noting.twin = 1";

        $beard = "";
        if (isset($_POST["beard"])) $beard = " AND noting.beard = 1";

        $body_double = "";
        if (isset($_POST["body_double"])) $body_double = " AND noting.body_double = 1";

        $body_double_artist = "";
        if ($_POST["body_double_artist"] != "") $langauge = " AND noting.body_double_artist like '%" . $this->clean_string($_POST["body_double_artist"]) . "%'";


        $instagram_follower = "";
        if ($_POST["instagram_follower"] != "") $instagram_follower = " AND media.instagram_follower = '" . $this->clean_string($_POST["instagram_follower"]) . "'";

        $instagram_verified = "";
        if (isset($_POST["instagram_verified"])) $instagram_verified = " AND media.instagram_verified = 1";

        $agency = "";
        if (isset($_POST["agency"])) $agency = " AND user_about.agency = 1";

        // registered as ---
        $type = "";
        if (isset($_POST["actor_type"])) {
            $type = " AND ( user_actor_type.actor_type_id = '' ";

            for ($i = 0; $i < count($_POST["actor_type"]); $i++) {
                $type .= " OR user_actor_type.actor_type_id = " . $this->clean_string($_POST["actor_type"][$i]);
            }
            $type .= " )";
        }

        $interests = "";
        if (isset($_POST["fields_of_interests"])) {
            $interests = " AND ( user_interest.interest_id = '' ";

            foreach ($_POST["fields_of_interests"] as $key => $value) {
                $interests .= " OR user_interest.interest_id = " . $this->clean_string($key);
            }
            $interests .= " )";
        }

        $internal_status = "";
        if (isset($_POST["internal_status"]) && $_POST["internal_status"] != "None") $internal_status = " AND users.internal_status = '" . $this->clean_string($_POST["internal_status"]) . "'";

        $is_popular = "";
        if (isset($_POST["is_popular"])) $is_popular = " AND users.is_popular = 1";

        $is_celebrity = "";
        if (isset($_POST["is_celebrity"])) $is_celebrity = " AND users.is_celebrity = 1";

        $internal_rank = "";
        if (isset($_POST["internal_rank"]) && $_POST["internal_rank"]) {
            $internal_rank = " AND users.internal_rank = '" . $this->clean_string($_POST["internal_rank"]) . "'";
        }


        $conn = $this->get_con();
        if (!$conn) {
            return $this->set_last_error("Connection does not exists");
        }


        $sql .= $gender;
        $sql .= $minage;
        $sql .= $maxage;
        $sql .= $country;
        $sql .= $state;
        $sql .= $city;
        $sql .= $langauge;
        $sql .= $other_languages;
        $sql .= $ethnicity;
        $sql .= $minheight;
        $sql .= $maxheight;
        $sql .= $hair_length;
        $sql .= $hair_color;
        $sql .= $skin_color;
        $sql .= $eye_color;
        $sql .= $body_type;
        $sql .= $experience;
        $sql .= $bold_scenes;
        $sql .= $south_movies;
        $sql .= $music_instruments;
        $sql .= $dance_styles;
        $sql .= $sports;
        $sql .= $special_skills;
        $sql .= $hobbies;
        $sql .= $braces;
        $sql .= $glasses;
        $sql .= $hearing_impared;
        $sql .= $handicap;
        $sql .= $tattoo;
        $sql .= $ride_bike;
        $sql .= $ride_car;
        $sql .= $driver_license;
        $sql .= $passport;
        $sql .= $union_card;
        $sql .= $twin;
        $sql .= $beard;
        $sql .= $body_double;
        $sql .= $body_double_artist;
        $sql .= $instagram_follower;
        $sql .= $agency;
        $sql .= $type;
        $sql .= $interests;
        $sql .= $theatre_experience;
        $sql .= $formal_training;
        $sql .= $below_one_year;
        $sql .= $instagram_verified;

        $sql .= $internal_status;
        $sql .= $internal_rank;
        $sql .= $is_celebrity;
        $sql .= $is_popular;

        //var_dump($sql);

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();

        return $data;
    }
}