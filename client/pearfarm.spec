<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 syntax=php: */

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('heartbeat_client')
             ->setChannel('apinstein.pearfarm.org')
             ->setSummary('Client for devops heartbeat system.')
             ->setDescription('Command line client for pinging a devops-status instance.')
             ->setReleaseVersion('0.0.5')
             ->setReleaseStability('stable')
             ->setApiVersion('0.0.3')
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'Alan Pinstein', 'apinstein', 'apinstein@mac.com')
             ->addGitFiles()
             ->addExecutable('heartbeat_client')
             ;
