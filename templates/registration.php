<?php
$error = false;
$content = '';

// form was submitted so we process the form
if($input->post->submit) {

        $ldapHelper = $modules->get("ldapHelper");
         //Sanatize and assign variables data before creating user.
        $nuser["username"] = $sanitizer->username($input->post->username);
        $nuser["email"] = $sanitizer->email($input->post->email);
        $nuser["password"] = $sanitizer->text($input->post->password);
        $nuser["firstname"] = $sanitizer->text($input->post->firstname);
        $nuser["lastname"] = $sanitizer->text($input->post->lastname);

        if(wire('users')->get($nuser["username"]) instanceof NullPage){
          if($ldapHelper->ldapHelperRegistradeUser($nuser)){
            $new_user = new User();
            $new_user->of(false);
            $new_user->name = $nuser["username"];
            $new_user->email = $nuser["email"];
            $new_user->pass = $nuser["password"];
            $new_user->addRole("user");
            $new_user->firstname = $nuser["firstname"];
            $new_user->lastname = $nuser["lastname"];
            $new_user->admin_theme = "AdminThemeReno";
            $new_user->authkey = base64_encode($nuser["username"].$today);
            $new_user->save();
            $new_user->of(true);

            $content .= "<div data-alert class='alert-box success radius'>Die Registrierung war erfolgreich, du erhälst nun eine E-Mail mit einem Aktivierungslink.</div>";
          } else {
            $content .= "<div data-alert class='alert-box alert'>
            Das Anlegen eines LDAP Users hat leider nicht funktioniert. Bitte versuche es zu einem späteren Zeitpunkt noch einmal.
            </div> ";
          }
        } else {
          $content .= "<div data-alert class='alert-box alert'>
                        Der Benutzername <strong>{$nuser['username']}</strong> ist schon vergeben!
                      </div>";
        }
} elseif($input->get->authorize) {
  $auth_user = $users->get("authorize={$input->get->authorize}");
  if(!$auth_user instanceof NullPage){
    if($auth_user->auth == true){
      $contant .="<div data-alert class='alert-box alert'>
        Dein Account wurde bereits Aktiviert.
      </div>";
    } else {
      $auth_user->of(false);
      $auth_user->auth = true;
      $auth_user->save();
      $auth_user->of(true);
    }
  }
}


if(!wire('user')->isLoggedin() && !wire('input')->post->submit){
  $content .= renderPage('registration_form');
} elseif(!wire('input')->post->submit) {
  $content .= "<div data-alert class='alert-box alert'>
    Du bist eingeloggt!
  </div> ";
}

$content = "<div id='article' class='row'> $content </div>";
