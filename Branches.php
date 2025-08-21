<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : branches (branchesController)
 * Branches Class to control branches related operations.
 * @author : Ashish Singh
 * @version : 1.0
 * @since : 01 March 2023
 */
class Branches extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Branches_model', 'bm');
        $this->isLoggedIn();
        $this->module = 'Branches';
		 $this->load->library('pagination');
    }

    /**
     * This is default routing method
     * It routes to default listing page
     */
    public function index()
    {
        redirect('branches/branchesListing');
    }
    
    /**
     * This function is used to load the branches list
     */
   
public function branchesListing()
{
    $userId = $this->session->userdata('userId');
    $userRole = $this->session->userdata('role');

    // Filters
    $franchiseFilter = $this->input->get('franchiseNumber');
    $statusFilter = $this->input->get('currentStatus');
    $growthManagerFilter = $this->input->get('growthManager');
    $searchText = $this->input->get('searchText');

    if ($this->input->get('resetFilter') == '1') {
        $franchiseFilter = '';
        $statusFilter = '';
        $growthManagerFilter = '';
        $searchText = '';
    }

    // Preserve filters
    $data['searchText'] = $searchText;
    $data['franchiseFilter'] = $franchiseFilter;
    $data['statusFilter'] = $statusFilter;
    $data['growthManagerFilter'] = $growthManagerFilter;

    // Pagination settings
    $config = array();
    $config["per_page"] = 10;
    $config["page_query_string"] = TRUE;
    $config["query_string_segment"] = "per_page";

    // Build query string for filters
    $queryParams = array();
    if ($franchiseFilter) $queryParams['franchiseNumber'] = $franchiseFilter;
    if ($statusFilter) $queryParams['currentStatus'] = $statusFilter;
    if ($growthManagerFilter) $queryParams['growthManager'] = $growthManagerFilter;
    if ($searchText) $queryParams['searchText'] = $searchText;
    $queryString = http_build_query($queryParams);

    $config["base_url"] = base_url() . "branches/branchesListing" . (!empty($queryString) ? "?" . $queryString : '');

    // Get total rows
    if (in_array($userRole, ['1', '2', '13', '14', '15', '16', '17', '18', '19', '20', '21', '23', '24', '27', '30', '33'])) {
        $config["total_rows"] = $this->bm->get_count($franchiseFilter, $userRole, $userId, $statusFilter, $searchText, $growthManagerFilter);
    } else {
        $franchiseNumber = $this->bm->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            $config["total_rows"] = $this->bm->get_count_by_franchise($franchiseNumber, $franchiseFilter, $statusFilter, $searchText, $growthManagerFilter);
        } else {
            $config["total_rows"] = 0;
        }
    }

    $this->pagination->initialize($config);
    $page = ($this->input->get('per_page')) ? $this->input->get('per_page') : 0;

    // Get paginated data
    if (in_array($userRole, ['1', '2', '13', '14', '15', '16', '17', '18', '19', '20', '21', '23', '24', '27', '30', '33'])) {
        $data["records"] = $this->bm->get_data($config["per_page"], $page, $franchiseFilter, $userRole, $userId, $statusFilter, $searchText, $growthManagerFilter);
    } else {
        $franchiseNumber = $this->bm->getFranchiseNumberByUserId($userId);
        if ($franchiseNumber) {
            $data["records"] = $this->bm->get_data_by_franchise($franchiseNumber, $config["per_page"], $page, $franchiseFilter, $statusFilter, $searchText, $growthManagerFilter);
        } else {
            $data["records"] = [];
        }
    }

    // Pagination info
    $data["links"] = $this->pagination->create_links();
    $data["start"] = $page + 1;
    $data["end"] = min($page + $config["per_page"], $config["total_rows"]);
    $data["total_records"] = $config["total_rows"];

    // Dropdown data
    $data['branchDetail'] = $this->bm->getBranchesFranchiseNumber();
    $data['growthManagers'] = $this->bm->getGrowthManagers(); // Must return list of growth managers

    // Load view
    $this->loadViews("branches/list", $this->global, $data, NULL);
}



    /**
     * This function is used to load the add new form
     */
    function add()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $data['users'] = $this->bm->getUser();
            $data['LDusers'] = $this->bm->getLDUser();
            $data['dusers'] = $this->bm->getDUser();
            $data['ACusers'] = $this->bm->getACUser();
            $data['ADMusers'] = $this->bm->getADMUser();
            $data['DISusers'] = $this->bm->getDISUser();
            $data['ATMusers'] = $this->bm->getATMUser();
            $data['MATusers'] = $this->bm->getMATUser();
            $data['DMusers'] = $this->bm->getDMUser();
            $data['TRMusers'] = $this->bm->getTRMUser();
            $data['SMDusers'] = $this->bm->getSMDuser();
            $data['user'] = $this->bm->getAllUserRole();
            $this->global['pageTitle'] = 'CodeInsect : Add New branches';

            $this->loadViews("branches/add", $this->global,$data, NULL);
        }
    }
    
    
    /**
     * This function is used to add new user to the system
     */
    function addNewBranches()
    {
        if(!$this->hasCreateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('applicantName','Applicant Name','trim|required|max_length[225]');
            $this->form_validation->set_rules('branchAddress','Address','trim|required|max_length[1024]');
            $this->form_validation->set_rules('mobile','Mobile Number','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->add();
            }
            else
            {
                $applicantName = $this->security->xss_clean($this->input->post('applicantName') ?? '');
                $branchAddress = $this->security->xss_clean($this->input->post('branchAddress') ?? '');
                $mobile = $this->security->xss_clean($this->input->post('mobile') ?? '');
                $branchEmail = $this->security->xss_clean($this->input->post('branchEmail') ?? '');
                $branchcityName = $this->security->xss_clean($this->input->post('branchcityName') ?? '');
                $branchSalesDoneby = $this->security->xss_clean($this->input->post('branchSalesDoneby') ?? '');
                $branchAmountReceived = $this->security->xss_clean($this->input->post('branchAmountReceived') ?? '');
                $branchState = $this->security->xss_clean($this->input->post('branchState') ?? '');
                $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned')?? '');
                $branchFranchiseAssignedDesigning = $this->security->xss_clean($this->input->post('branchFranchiseAssignedDesigning')?? '');
                $branchFranchiseAssignedLegalDepartment = $this->security->xss_clean($this->input->post('branchFranchiseAssignedLegalDepartment')?? '');
                $branchFrAssignedAccountsDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedAccountsDepartment')?? '');
                $branchFrAssignedDispatchDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedDispatchDepartment')?? '');
                $branchFrAssignedAdmintrainingDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedAdmintrainingDepartment')?? '');
                $branchFrAssignedAdmissionDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedAdmissionDepartment')?? '');
                $branchFrAssignedMaterialDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedMaterialDepartment')?? '');
                $branchFrAssignedDigitalDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedDigitalDepartment')?? '');
                $branchFrAssignedTrainingDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedTrainingDepartment')?? '');
                $branchFrAssignedSocialmediaDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedSocialmediaDepartment')?? '');
                $permanentAddress = $this->security->xss_clean($this->input->post('permanentAddress')?? '');
                $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber')?? '');
                $franchiseName = $this->security->xss_clean($this->input->post('franchiseName')?? '');
                $typeBranch = $this->security->xss_clean($this->input->post('typeBranch')?? '');
                $currentStatus = $this->security->xss_clean($this->input->post('currentStatus')?? '');
                $bookingDate = $this->security->xss_clean($this->input->post('bookingDate')?? '');
                $licenseNumber = $this->security->xss_clean($this->input->post('licenseNumber')?? '');
                $licenseSharedon = $this->security->xss_clean($this->input->post('licenseSharedon')?? '');
                $validFromDate = $this->security->xss_clean($this->input->post('validFromDate')?? '');
                $validTillDate = $this->security->xss_clean($this->input->post('validTillDate')?? '');
                $branchLocation = $this->security->xss_clean($this->input->post('branchLocation')?? '');
                $adminName = $this->security->xss_clean($this->input->post('adminName'));
                $adminContactNum = $this->security->xss_clean($this->input->post('adminContactNum')?? '');
                $additionalNumber = $this->security->xss_clean($this->input->post('additionalNumber')?? '');
                $officialEmailID = $this->security->xss_clean($this->input->post('officialEmailID')?? '');
                $personalEmailId = $this->security->xss_clean($this->input->post('personalEmailId')?? '');
                /*---Design-section--*/
                $biometricInstalled = $this->security->xss_clean($this->input->post('biometricInstalled')?? '');
                $biometricRemark = $this->security->xss_clean($this->input->post('biometricRemark')?? '');
                $biometricInstalledDate = $this->security->xss_clean($this->input->post('biometricInstalledDate')?? '');
                $camaraInstalled = $this->security->xss_clean($this->input->post('camaraInstalled')?? '');
                $camaraRemark = $this->security->xss_clean($this->input->post('camaraRemark')?? '');
                $camaraInstalledDate = $this->security->xss_clean($this->input->post('camaraInstalledDate')?? '');
                $eduMetaAppTraining = $this->security->xss_clean($this->input->post('eduMetaAppTraining')?? '');
                $AppTrainingRemark = $this->security->xss_clean($this->input->post('AppTrainingRemark')?? '');
                $AppTrainingRemarkDate = $this->security->xss_clean($this->input->post('AppTrainingRemarkDate')?? '');
                $congratulationsImg = $this->security->xss_clean($this->input->post('congratulationsImg')?? '');
                $brimguploadedFBStatus = $this->security->xss_clean($this->input->post('brimguploadedFBStatus')?? '');
                $brimguploadedFBDate = $this->security->xss_clean($this->input->post('brimguploadedFBDate')?? '');
                $brimguploadedInstaStatus = $this->security->xss_clean($this->input->post('brimguploadedInstaStatus')?? '');
                $brimguploadedInstaDate = $this->security->xss_clean($this->input->post('brimguploadedInstaDate')?? '');
                $admissionOpenimgStatus = $this->security->xss_clean($this->input->post('admissionOpenimgStatus')?? '');
                $staffHiringimgStatus = $this->security->xss_clean($this->input->post('staffHiringimgStatus')?? '');
                $newsletterMarch = $this->security->xss_clean($this->input->post('newsletterMarch')?? '');
                $newsletterJune = $this->security->xss_clean($this->input->post('newsletterJune')?? '');
                $newsletterSeptember = $this->security->xss_clean($this->input->post('newsletterSeptember')?? '');
                $newsletterDecember = $this->security->xss_clean($this->input->post('newsletterDecember')?? '');
                $OBirthDayImgStatus = $this->security->xss_clean($this->input->post('OBirthDayImgStatus')?? '');
                $OBirthDayImgSharedDtm = $this->security->xss_clean($this->input->post('OBirthDayImgSharedDtm')?? '');
                $OwnerAnnImgStatus = $this->security->xss_clean($this->input->post('OwnerAnnImgStatus')?? '');
                $OwnerAnnImgSharedDtm = $this->security->xss_clean($this->input->post('OwnerAnnImgSharedDtm')?? '');
                /*---End-Design-Section---*/
                /*---Start-Digital-Section--*/
                $facebookPageStatus = $this->security->xss_clean($this->input->post('facebookPageStatus')?? '');
                $facebookPageLink = $this->security->xss_clean($this->input->post('facebookPageLink')?? '');
                $facebookPageRemark = $this->security->xss_clean($this->input->post('facebookPageRemark')?? '');
                $googleMapLoc = $this->security->xss_clean($this->input->post('googleMapLoc')?? '');
                $googleMapLocLink = $this->security->xss_clean($this->input->post('googleMapLocLink')?? '');               
                $googleMapLocRemark = $this->security->xss_clean($this->input->post('googleMapLocRemark')?? '');
                $instagramPageStatus = $this->security->xss_clean($this->input->post('instagramPageStatus')?? '');
                $instagramPageID = $this->security->xss_clean($this->input->post('instagramPageID')?? '');
                $instagramPageRemark = $this->security->xss_clean($this->input->post('instagramPageRemark')?? '');
                $jdPageStatus = $this->security->xss_clean($this->input->post('jdPageStatus')?? '');
                $jdPageID = $this->security->xss_clean($this->input->post('jdPageID')?? '');
                $jdPageRemark = $this->security->xss_clean($this->input->post('jdPageRemark')?? '');
                $tweetPageStatus = $this->security->xss_clean($this->input->post('tweetPageStatus')?? '');
                $tweetPageID = $this->security->xss_clean($this->input->post('tweetPageID')?? '');
                $tweetPageRemark = $this->security->xss_clean($this->input->post('tweetPageRemark')?? '');
                $digiMarkCost = $this->security->xss_clean($this->input->post('digiMarkCost')?? '');
                $digiMarkStartDtm = $this->security->xss_clean($this->input->post('digiMarkStartDtm')?? '');
                $digiMarkEndDtm = $this->security->xss_clean($this->input->post('digiMarkEndDtm')?? '');
                $digiMarkReamrk = $this->security->xss_clean($this->input->post('digiMarkReamrk')?? '');
                $insfeedvideoUplodFB = $this->security->xss_clean($this->input->post('insfeedvideoUplodFB')?? '');
                $insfeedvideoUplodYoutube = $this->security->xss_clean($this->input->post('insfeedvideoUplodYoutube')?? '');
                $insfeedvideoUplodInsta = $this->security->xss_clean($this->input->post('insfeedvideoUplodInsta')?? '');
                /*---End-Digital-Section--*/
                $branchLocAddressPremise = $this->security->xss_clean($this->input->post('branchLocAddressPremise')?? '');
                $addOfFranchise = $this->security->xss_clean($this->input->post('addOfFranchise'));
                $gstNumber = $this->security->xss_clean($this->input->post('gstNumber')?? '');
                $undertakingCommitmentSupport = $this->security->xss_clean($this->input->post('undertakingCommitmentSupport')?? '');
                $amcAmount = $this->security->xss_clean($this->input->post('amcAmount')?? '');
                $invoiceNumber = $this->security->xss_clean($this->input->post('invoiceNumber')?? '');
                $agreementTenure = $this->security->xss_clean($this->input->post('agreementTenure')?? '');
                $salesExecutive = $this->security->xss_clean($this->input->post('salesExecutive')?? '');
                $salesTeamlead = $this->security->xss_clean($this->input->post('salesTeamlead')?? '');
                $Manual1 = $this->security->xss_clean($this->input->post('Manual1')?? '');
                $Manual2 = $this->security->xss_clean($this->input->post('Manual2')?? '');
                $Manual3 = $this->security->xss_clean($this->input->post('Manual3')?? '');
                $Reference = $this->security->xss_clean($this->input->post('Reference')?? '');
                $installationTentativeDate = $this->security->xss_clean($this->input->post('installationTentativeDate')?? '');
                $formsDocumentsCompleted = $this->security->xss_clean($this->input->post('formsDocumentsCompleted')?? '');
                $setUpInstallation = $this->security->xss_clean($this->input->post('setUpInstallation')?? '');
                $branchAnniversaryDate = $this->security->xss_clean($this->input->post('branchAnniversaryDate')?? '');
                $admissionCracked = $this->security->xss_clean($this->input->post('admissionCracked')?? '');
                $teacherRecruitment = $this->security->xss_clean($this->input->post('teacherRecruitment')?? '');
                $pgDecidedFee = $this->security->xss_clean($this->input->post('pgDecidedFee')?? '');
                $nurseryDecidedFee = $this->security->xss_clean($this->input->post('nurseryDecidedFee')?? '');
                $KG1DecidedFee = $this->security->xss_clean($this->input->post('KG1DecidedFee')?? '');
                $KG2DecidedFee = $this->security->xss_clean($this->input->post('KG2DecidedFee')?? '');
                $feeSharedStatus = $this->security->xss_clean($this->input->post('feeSharedStatus')?? '');
                $feesRemark = $this->security->xss_clean($this->input->post('feesRemark')?? '');
                $addmissionPG = $this->security->xss_clean($this->input->post('addmissionPG')?? '');
                $addmissionNursary = $this->security->xss_clean($this->input->post('addmissionNursary')?? '');
                $addmissionKg1 = $this->security->xss_clean($this->input->post('addmissionKg1')?? '');
                $addmissionKg2 = $this->security->xss_clean($this->input->post('addmissionKg2')?? '');
                $addmission1st = $this->security->xss_clean($this->input->post('addmission1st')?? '');
                $addmission2nd = $this->security->xss_clean($this->input->post('addmission2nd')?? '');
                $totalAddmission = $this->security->xss_clean($this->input->post('totalAddmission')?? '');
                $addmissionCounselor = $this->security->xss_clean($this->input->post('addmissionCounselor')?? '');
                $lastDiscussaddmission = $this->security->xss_clean($this->input->post('lastDiscussaddmission')?? '');
                $addmissionSheetlink = $this->security->xss_clean($this->input->post('addmissionSheetlink')?? '');
                $dateexlSheetshared = $this->security->xss_clean($this->input->post('dateexlSheetshared')?? '');
                $lastInteractiondate = $this->security->xss_clean($this->input->post('lastInteractiondate')?? '');
                $lastDiscussionby = $this->security->xss_clean($this->input->post('lastDiscussionby')?? '');
                $lastInteractioncomment = $this->security->xss_clean($this->input->post('lastInteractioncomment')?? '');
                $agreementDraftdate = $this->security->xss_clean($this->input->post('agreementDraftdate')?? '');
                $branchLandline  = $this->security->xss_clean($this->input->post('branchLandline')?? '');
                $additionalName = $this->security->xss_clean($this->input->post('additionalName')?? '');
                $finalPaydeadline = $this->security->xss_clean($this->input->post('finalPaydeadline')?? '');
                $BranchSpecialNoteSales = $this->security->xss_clean($this->input->post('BranchSpecialNoteSales')?? '');
                $completeFranchiseAmt = $this->security->xss_clean($this->input->post('completeFranchiseAmt')?? '');
                $confirmationAmt33kGST = $this->security->xss_clean($this->input->post('confirmationAmt33kGST')?? '');
                $happinessLevelbranch = $this->security->xss_clean($this->input->post('happinessLevelbranch')?? '');
                $DesignsPromotional = $this->security->xss_clean($this->input->post('DesignsPromotional')?? '');
                $DesignsPromotionalRemark = $this->security->xss_clean($this->input->post('DesignsPromotionalRemark')?? '');
                $BranchSpecialNote = $this->security->xss_clean($this->input->post('BranchSpecialNote')?? '');
                $OwnerAnniversery = $this->security->xss_clean($this->input->post('OwnerAnniversery')?? '');
                $welcomeCall = $this->security->xss_clean($this->input->post('welcomeCall')?? '');
                $welcomeMail = $this->security->xss_clean($this->input->post('welcomeMail')?? '');
                $whatsappGroup = $this->security->xss_clean($this->input->post('whatsappGroup')?? '');
                $whatsappGroupRemark = $this->security->xss_clean($this->input->post('whatsappGroupRemark')?? '');
                $whatsappGroupdate = $this->security->xss_clean($this->input->post('whatsappGroupdate')?? '');
                $interactionMeeting = $this->security->xss_clean($this->input->post('interactionMeeting')?? '');
                $interactionMeetingRemark = $this->security->xss_clean($this->input->post('interactionMeetingRemark')?? '');
                $undertakingCommitment = $this->security->xss_clean($this->input->post('undertakingCommitment')?? '');
                $onboardingForm = $this->security->xss_clean($this->input->post('onboardingForm')?? '');
                $onboardingFormReceived = $this->security->xss_clean($this->input->post('onboardingFormReceived')?? '');
                $onboardingFormRemark = $this->security->xss_clean($this->input->post('onboardingFormRemark')?? '');
                $installationRequirementmail = $this->security->xss_clean($this->input->post('installationRequirementmail')?? '');
                $installationRequirementmailRemark = $this->security->xss_clean($this->input->post('installationRequirementmailRemark')?? '');
                $finalAgreementShared = $this->security->xss_clean($this->input->post('finalAgreementShared')?? '');
                $agreementDraftReceiveddate = $this->security->xss_clean($this->input->post('agreementDraftReceiveddate')?? '');
                $compFileSubmit = $this->security->xss_clean($this->input->post('compFileSubmit')?? '');
                $fileCLoserDate = $this->security->xss_clean($this->input->post('fileCLoserDate')?? '');
                $branchStatusRemark = $this->security->xss_clean($this->input->post('branchStatusRemark')?? '');
                $officialemailshared = $this->security->xss_clean($this->input->post('officialemailshared')?? '');
                 $inaugurationDate = $this->security->xss_clean($this->input->post('inaugurationDate')?? '');
               // $adminTraining = $this->security->xss_clean($this->input->post('adminTraining')?? '');
               
                $classroomDecoration = $this->security->xss_clean($this->input->post('classroomDecoration')?? '');
                $movieClub = $this->security->xss_clean($this->input->post('movieClub')?? '');
                $referEarn = $this->security->xss_clean($this->input->post('referEarn')?? '');
                $teacherInteraction = $this->security->xss_clean($this->input->post('teacherInteraction')?? '');
               $teacherInterview = $this->security->xss_clean($this->input->post('teacherInterview')?? '');
               $pongalWorkshop = $this->security->xss_clean($this->input->post('pongalWorkshop')?? '');
              $sankrantiWorkshop = $this->security->xss_clean($this->input->post('sankrantiWorkshop')?? '');
               $republicDayWorkshop = $this->security->xss_clean($this->input->post('republicDayWorkshop')?? '');
               $bridgeCourseCounselling = $this->security->xss_clean($this->input->post('bridgeCourseCounselling')?? '');
              
               $settlersProgram = $this->security->xss_clean($this->input->post('settlersProgram')?? '');
               $jollyPhonic = $this->security->xss_clean($this->input->post('jollyPhonic')?? '');
               $academicsMeetings = $this->security->xss_clean($this->input->post('academicsMeetings')?? '');
               
                $curiculumnShared = $this->security->xss_clean($this->input->post('curiculumnShared')?? '');
               $sharingAssessmentpapers = $this->security->xss_clean($this->input->post('sharingAssessmentpapers')?? '');
               $assessmentSharingemail = $this->security->xss_clean($this->input->post('assessmentSharingemail')?? '');
               $PTMscheduledate = $this->security->xss_clean($this->input->post('PTMscheduledate')?? '');
               $shadowPuppet = $this->security->xss_clean($this->input->post('shadowPuppet')?? '');
                $monthlyEventtraining = $this->security->xss_clean($this->input->post('monthlyEventtraining')?? '');
                $summertCampdate = $this->security->xss_clean($this->input->post('summertCampdate')?? '');
                $winterCampdate = $this->security->xss_clean($this->input->post('winterCampdate')?? '');


                 // New fields add training Yashi
               
                //end yashi
                /*Added Additional Field by Sales-04-Jul-2024 */
                $legalChargesSales = $this->security->xss_clean($this->input->post('legalChargesSales')?? '');
                $brSetupinsChargSales = $this->security->xss_clean($this->input->post('brSetupinsChargSales')?? '');
                $numInialKitSales = $this->security->xss_clean($this->input->post('numInialKitSales')?? '');
                $franchiseTenure = $this->security->xss_clean($this->input->post('franchiseTenure')?? '');
                /*End-Added Additional Field by Sales-04-Jul-2024 */
                $welComeFolderStatus = $this->security->xss_clean($this->input->post('welComeFolderStatus')?? '');
                $welComeFolderDtm = $this->security->xss_clean($this->input->post('welComeFolderDtm')?? '');
                $trainingAmount = $this->security->xss_clean($this->input->post('trainingAmount')?? '');
                $societyServiceamount = $this->security->xss_clean($this->input->post('societyServiceamount')?? '');
                $totalAmount = $this->security->xss_clean($this->input->post('totalAmount')?? '');
                $gstAmount = $this->security->xss_clean($this->input->post('gstAmount')?? '');
                $totalfranchisegstFund = $this->security->xss_clean($this->input->post('totalfranchisegstFund')?? '');
                $legalCharges = $this->security->xss_clean($this->input->post('legalCharges')?? '');
                $legalChargesdue = $this->security->xss_clean($this->input->post('legalChargesdue')?? '');
                $totalgstCharges = $this->security->xss_clean($this->input->post('totalgstCharges')?? '');
                $totalPaidamount = $this->security->xss_clean($this->input->post('totalPaidamount')?? '');
                $dueFranchiseamt = $this->security->xss_clean($this->input->post('dueFranchiseamt')?? '');
                $kitCharges = $this->security->xss_clean($this->input->post('kitCharges')?? '');
                $numinitialKit = $this->security->xss_clean($this->input->post('numinitialKit')?? '');
                $totalKitsamt = $this->security->xss_clean($this->input->post('totalKitsamt')?? '');
                $kitamtReceived = $this->security->xss_clean($this->input->post('kitamtReceived')?? '');
                $dueKitamount = $this->security->xss_clean($this->input->post('dueKitamount')?? '');
                $installationDate = $this->security->xss_clean($this->input->post('installationDate')?? '');
                 // End 
                  $bulletinBoard = $this->security->xss_clean($this->input->post('bulletinBoard')?? '');
                $bridgeCourse = $this->security->xss_clean($this->input->post('bridgeCourse')?? '');
                 $timeDisclipineemail = $this->security->xss_clean($this->input->post('timeDisclipineemail')?? '');
                $uniformDisclipineemail = $this->security->xss_clean($this->input->post('uniformDisclipineemail')?? '');
                
                $holidaEventlisting = $this->security->xss_clean($this->input->post('holidaEventlisting')?? '');
                $offerName = $this->security->xss_clean($this->input->post('offerName')?? '');
                $offerPlanname = $this->security->xss_clean($this->input->post('offerPlanname')?? '');
                $discountAmount = $this->security->xss_clean($this->input->post('discountAmount')?? '');
                $finalAmount = $this->security->xss_clean($this->input->post('finalAmount')?? '');
                /*Added Additional Field by Sales-04-Jul-2024 */
                $legalChargesSales = $this->security->xss_clean($this->input->post('legalChargesSales')?? '');
                $brSetupinsChargSales = $this->security->xss_clean($this->input->post('brSetupinsChargSales')?? '');
                $numInialKitSales = $this->security->xss_clean($this->input->post('numInialKitSales')?? '');
                $franchiseTenure = $this->security->xss_clean($this->input->post('franchiseTenure')?? '');
                /*End-Added Additional Field by Sales-04-Jul-2024 */
                $welComeFolderStatus = $this->security->xss_clean($this->input->post('welComeFolderStatus')?? '');
                $welComeFolderDtm = $this->security->xss_clean($this->input->post('welComeFolderDtm')?? '');
                $trainingAmount = $this->security->xss_clean($this->input->post('trainingAmount')?? '');
                $societyServiceamount = $this->security->xss_clean($this->input->post('societyServiceamount')?? '');
                $totalAmount = $this->security->xss_clean($this->input->post('totalAmount')?? '');
                $gstAmount = $this->security->xss_clean($this->input->post('gstAmount')?? '');
                $totalfranchisegstFund = $this->security->xss_clean($this->input->post('totalfranchisegstFund')?? '');
                $legalCharges = $this->security->xss_clean($this->input->post('legalCharges')?? '');
                $legalChargesdue = $this->security->xss_clean($this->input->post('legalChargesdue')?? '');
                $totalgstCharges = $this->security->xss_clean($this->input->post('totalgstCharges')?? '');
                $totalPaidamount = $this->security->xss_clean($this->input->post('totalPaidamount')?? '');
                $dueFranchiseamt = $this->security->xss_clean($this->input->post('dueFranchiseamt')?? '');
                $kitCharges = $this->security->xss_clean($this->input->post('kitCharges')?? '');
                $numinitialKit = $this->security->xss_clean($this->input->post('numinitialKit')?? '');
                $totalKitsamt = $this->security->xss_clean($this->input->post('totalKitsamt')?? '');
                $kitamtReceived = $this->security->xss_clean($this->input->post('kitamtReceived')?? '');
                $dueKitamount = $this->security->xss_clean($this->input->post('dueKitamount')?? '');
                $installationDate = $this->security->xss_clean($this->input->post('installationDate')?? '');
                $finaltotalamtDue = $this->security->xss_clean($this->input->post('finaltotalamtDue')?? '');
                $specialRemark = $this->security->xss_clean($this->input->post('specialRemark')?? '');
                $transporttravCharge = $this->security->xss_clean($this->input->post('transporttravCharge')?? '');
                $brsetupinstachargReceived = $this->security->xss_clean($this->input->post('brsetupinstachargReceived')?? '');
                $brsetupinstachargDue = $this->security->xss_clean($this->input->post('brsetupinstachargDue')?? '');
                $travelAmount = $this->security->xss_clean($this->input->post('travelAmount')?? '');
                $receivedtravelAmount = $this->security->xss_clean($this->input->post('receivedtravelAmount')?? '');
                $duetravelAmount = $this->security->xss_clean($this->input->post('duetravelAmount')?? '');
                $transportCharges = $this->security->xss_clean($this->input->post('transportCharges')?? '');
                $transportAmtreceived = $this->security->xss_clean($this->input->post('transportAmtreceived')?? '');
                $duetransportCharges = $this->security->xss_clean($this->input->post('duetransportCharges')?? '');
                /*---New-Accounts--*/
                $ledgerMarch = $this->security->xss_clean($this->input->post('ledgerMarch')?? '');
                $ledgerJune = $this->security->xss_clean($this->input->post('ledgerJune')?? '');
                $ledgerSeptember = $this->security->xss_clean($this->input->post('ledgerSeptember')?? '');
                $ledgerDecember = $this->security->xss_clean($this->input->post('ledgerDecember')?? '');
                $reminderAMCStatus1Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus1Dec')?? '');
                $reminderAMCStatus10Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus10Dec')?? '');
                $reminderAMCStatus15Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus15Dec')?? '');
                $reminderAMCStatus19Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus19Dec')?? '');
                $reminderAMCStatus20Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus20Dec')?? '');
                $RemarkforAMCmail = $this->security->xss_clean($this->input->post('RemarkforAMCmail')?? '');
                $InvoiceAMCClearance = $this->security->xss_clean($this->input->post('InvoiceAMCClearance')?? '');
                $PenaltyMailnoncle = $this->security->xss_clean($this->input->post('PenaltyMailnoncle')?? '');
                $invoiceNumberAll = $this->security->xss_clean($this->input->post('invoiceNumberAll')?? '');
                /*---End-New-Accounts--*/
                $upgradeUptoclass = $this->security->xss_clean($this->input->post('upgradeUptoclass')?? '');
                $branchStatus = $this->security->xss_clean($this->input->post('branchStatus')?? '');
                $brInstallationStatus = $this->security->xss_clean($this->input->post('brInstallationStatus')?? '');
                $undertakingAck = $this->security->xss_clean($this->input->post('undertakingAck')?? '');
                $optOnlineMarketing = $this->security->xss_clean($this->input->post('optOnlineMarketing')?? '');
                /*---Despatch--*/
                $insmatDispatchdate = $this->security->xss_clean($this->input->post('insmatDispatchdate')?? '');
                $DetailsReceiptmail = $this->security->xss_clean($this->input->post('DetailsReceiptmail')?? '');
                $ConfBrinsScheduledemail = $this->security->xss_clean($this->input->post('ConfBrinsScheduledemail')?? '');
                $Materialrecdate = $this->security->xss_clean($this->input->post('Materialrecdate')?? '');
                $BrinsScheduleddate = $this->security->xss_clean($this->input->post('BrinsScheduleddate')?? '');
                $BrinsScheduledemail = $this->security->xss_clean($this->input->post('BrinsScheduledemail')?? '');
                $brInstalationRemark = $this->security->xss_clean($this->input->post('brInstalationRemark')?? '');
                $videoFeedbackbr = $this->security->xss_clean($this->input->post('videoFeedbackbr')?? '');
                $writtenFeedbackbr = $this->security->xss_clean($this->input->post('writtenFeedbackbr')?? '');
                $ShoppinPortSharedDate = $this->security->xss_clean($this->input->post('ShoppinPortSharedDate')?? '');
                $ShoppinPortTraining = $this->security->xss_clean($this->input->post('ShoppinPortTraining')?? '');
                $ShoppinPortTrainingDate = $this->security->xss_clean($this->input->post('ShoppinPortTrainingDate')?? '');
                $ShoppinPortRemark = $this->security->xss_clean($this->input->post('ShoppinPortRemark')?? '');
                $returnItems = $this->security->xss_clean($this->input->post('returnItems')?? '');
                $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch')?? '');
                $NumOfBoxes = $this->security->xss_clean($this->input->post('NumOfBoxes')?? '');
                $PoDNum = $this->security->xss_clean($this->input->post('PoDNum')?? '');
                $SpecificGiftOffer = $this->security->xss_clean($this->input->post('SpecificGiftOffer')?? '');
                $ConfBrInsOverPhone = $this->security->xss_clean($this->input->post('ConfBrInsOverPhone')?? '');
                $shortComming = $this->security->xss_clean($this->input->post('shortComming')?? '');
                $solutionShortComming = $this->security->xss_clean($this->input->post('solutionShortComming')?? '');
                $customWebsiteLink = $this->security->xss_clean($this->input->post('customWebsiteLink')?? '');

                /*--End-Despatch--*/


                
                $branchesInfo = array('applicantName'=>$applicantName, 'mobile'=>$mobile, 'branchEmail'=>$branchEmail, 'branchcityName'=>$branchcityName, 'branchState'=>$branchState, 'branchSalesDoneby'=>$branchSalesDoneby, 'branchAmountReceived'=>$branchAmountReceived, 'branchFranchiseAssigned'=>$branchFranchiseAssigned,'branchFranchiseAssignedDesigning'=>$branchFranchiseAssignedDesigning,'branchFranchiseAssignedLegalDepartment'=>$branchFranchiseAssignedLegalDepartment, 'branchFrAssignedAccountsDepartment'=>$branchFrAssignedAccountsDepartment, 'branchFrAssignedDispatchDepartment'=>$branchFrAssignedDispatchDepartment, 'branchFrAssignedAdmintrainingDepartment'=>$branchFrAssignedAdmintrainingDepartment, 'branchFrAssignedAdmissionDepartment'=>$branchFrAssignedAdmissionDepartment,'branchFrAssignedMaterialDepartment'=>$branchFrAssignedMaterialDepartment,'branchFrAssignedDigitalDepartment'=>$branchFrAssignedDigitalDepartment, 'branchFrAssignedTrainingDepartment'=>$branchFrAssignedTrainingDepartment,'branchFrAssignedSocialmediaDepartment'=>$branchFrAssignedSocialmediaDepartment, 'branchAddress'=>$branchAddress, 'permanentAddress'=>$permanentAddress, 'franchiseNumber'=>$franchiseNumber, 'franchiseName'=>$franchiseName, 'typeBranch'=>$typeBranch, 'currentStatus'=>$currentStatus, 'bookingDate'=>$bookingDate, 'licenseNumber'=>$licenseNumber, 'licenseSharedon'=>$licenseSharedon, 'validFromDate'=>$validFromDate, 'validTillDate'=>$validTillDate, 'branchLocation'=>$branchLocation, 'adminName'=>$adminName, 'adminContactNum'=>$adminContactNum, 'additionalNumber'=>$additionalNumber, 'officialEmailID'=>$officialEmailID, 'personalEmailId'=>$personalEmailId, 'biometricInstalled'=>$biometricInstalled, 'biometricRemark'=>$biometricRemark, 'biometricInstalledDate'=>$biometricInstalledDate, 'camaraInstalled'=>$camaraInstalled, 'camaraInstalledDate'=>$camaraInstalledDate, 'camaraRemark'=>$camaraRemark, 'eduMetaAppTraining'=>$eduMetaAppTraining, 'AppTrainingRemark'=>$AppTrainingRemark, 'AppTrainingRemarkDate'=>$AppTrainingRemarkDate, 'congratulationsImg'=>$congratulationsImg, 'brimguploadedFBStatus'=>$brimguploadedFBStatus, 'brimguploadedFBDate'=>$brimguploadedFBDate, 'brimguploadedInstaStatus'=>$brimguploadedInstaStatus, 'brimguploadedInstaDate'=>$brimguploadedInstaDate, 'admissionOpenimgStatus'=>$admissionOpenimgStatus, 'staffHiringimgStatus'=>$staffHiringimgStatus, 'newsletterMarch'=>$newsletterMarch, 'newsletterJune'=>$newsletterJune, 'newsletterSeptember'=>$newsletterSeptember, 'newsletterDecember'=>$newsletterDecember, 'OBirthDayImgStatus'=>$OBirthDayImgStatus, 'OBirthDayImgSharedDtm'=>$OBirthDayImgSharedDtm, 'OwnerAnnImgStatus'=>$OwnerAnnImgStatus, 'OwnerAnnImgSharedDtm'=>$OwnerAnnImgSharedDtm, 'facebookPageStatus'=>$facebookPageStatus, 'facebookPageLink'=>$facebookPageLink, 'facebookPageRemark'=>$facebookPageRemark, 'googleMapLoc'=>$googleMapLoc, 'googleMapLocLink'=>$googleMapLocLink, 'googleMapLocRemark'=>$googleMapLocRemark, 'instagramPageStatus'=>$instagramPageStatus, 'instagramPageID'=>$instagramPageID, 'instagramPageRemark'=>$instagramPageRemark, 'jdPageStatus'=>$jdPageStatus, 'jdPageID'=>$jdPageID, 'jdPageRemark'=>$jdPageRemark, 'tweetPageStatus'=>$tweetPageStatus, 'tweetPageID'=>$tweetPageID, 'tweetPageRemark'=>$tweetPageRemark, 'digiMarkCost'=>$digiMarkCost, 'digiMarkStartDtm'=>$digiMarkStartDtm, 'digiMarkEndDtm'=>$digiMarkEndDtm, 'digiMarkReamrk'=>$digiMarkReamrk, 'insfeedvideoUplodFB'=>$insfeedvideoUplodFB, 'insfeedvideoUplodYoutube'=>$insfeedvideoUplodYoutube, 'insfeedvideoUplodInsta'=>$insfeedvideoUplodInsta, 'branchLocAddressPremise'=>$branchLocAddressPremise, 'addOfFranchise'=>$addOfFranchise, 'gstNumber'=>$gstNumber, 'undertakingCommitmentSupport'=>$undertakingCommitmentSupport, 'amcAmount'=>$amcAmount, 'invoiceNumber'=>$invoiceNumber, 'agreementTenure'=>$agreementTenure, 'salesExecutive'=>$salesExecutive, 'salesTeamlead'=>$salesTeamlead, 'Manual1'=>$Manual1, 'Manual2'=>$Manual2, 'Manual3'=>$Manual3, 'Reference'=>$Reference, 'installationTentativeDate'=>$installationTentativeDate, 'formsDocumentsCompleted'=>$formsDocumentsCompleted, 'setUpInstallation'=>$setUpInstallation, 'branchAnniversaryDate'=>$branchAnniversaryDate, 'admissionCracked'=>$admissionCracked, 'teacherRecruitment'=>$teacherRecruitment, 'pgDecidedFee'=>$pgDecidedFee, 'nurseryDecidedFee'=>$nurseryDecidedFee, 'KG1DecidedFee'=>$KG1DecidedFee, 'KG2DecidedFee'=>$KG2DecidedFee, 'feeSharedStatus'=>$feeSharedStatus, 'feesRemark'=>$feesRemark, 'addmissionPG' =>$addmissionPG, 'addmissionNursary' =>$addmissionNursary, 'addmissionKg1' =>$addmissionKg1, 'addmissionKg2' =>$addmissionKg2, 'addmission1st' =>$addmission1st, 'addmission2nd' =>$addmission2nd, 'totalAddmission' =>$totalAddmission, 'addmissionCounselor' =>$addmissionCounselor, 'lastDiscussaddmission' =>$lastDiscussaddmission, 'addmissionSheetlink' =>$addmissionSheetlink, 'dateexlSheetshared'=>$dateexlSheetshared, 'lastInteractiondate' =>$lastInteractiondate, 'lastDiscussionby' =>$lastDiscussionby, 'lastInteractioncomment' =>$lastInteractioncomment, 'agreementDraftdate' =>$agreementDraftdate, 'branchLandline'  =>$branchLandline, 'additionalName' =>$additionalName, 'finalPaydeadline' =>$finalPaydeadline, 'BranchSpecialNoteSales'=>$BranchSpecialNoteSales, 'completeFranchiseAmt' =>$completeFranchiseAmt, 'confirmationAmt33kGST' =>$confirmationAmt33kGST, 'happinessLevelbranch' =>$happinessLevelbranch, 'DesignsPromotional'=>$DesignsPromotional, 'DesignsPromotionalRemark'=>$DesignsPromotionalRemark, 'BranchSpecialNote'=>$BranchSpecialNote, 'OwnerAnniversery'=>$OwnerAnniversery, 'welcomeCall'=>$welcomeCall, 'welcomeMail'=>$welcomeMail, 'whatsappGroup'=>$whatsappGroup, 'whatsappGroupRemark'=>$whatsappGroupRemark, 'whatsappGroupdate'=>$whatsappGroupdate, 'interactionMeeting'=>$interactionMeeting, 'interactionMeetingRemark'=>$interactionMeetingRemark, 'undertakingCommitment'=>$undertakingCommitment, 'onboardingForm'=>$onboardingForm, 'onboardingFormReceived'=>$onboardingFormReceived, 'onboardingFormRemark'=>$onboardingFormRemark, 'installationRequirementmail'=>$installationRequirementmail, 'installationRequirementmailRemark'=>$installationRequirementmailRemark, 'finalAgreementShared'=>$finalAgreementShared, 'agreementDraftReceiveddate'=>$agreementDraftReceiveddate,'compFileSubmit'=>$compFileSubmit,'fileCLoserDate'=>$fileCLoserDate,'branchStatusRemark'=>$branchStatusRemark, 'officialemailshared'=>$officialemailshared, 'inaugurationDate'=>$inaugurationDate, 'classroomDecoration'=>$classroomDecoration, 'movieClub'=>$movieClub, 'referEarn'=>$referEarn, 'teacherInteraction'=>$teacherInteraction, 'teacherInterview'=>$teacherInterview, 'pongalWorkshop'=>$pongalWorkshop, 'sankrantiWorkshop'=>$sankrantiWorkshop, 'republicDayWorkshop'=>$republicDayWorkshop, 'bridgeCourseCounselling'=>$bridgeCourseCounselling, 'bulletinBoard'=>$bulletinBoard, 'bridgeCourse'=>$bridgeCourse, 'settlersProgram'=>$settlersProgram, 'jollyPhonic'=>$jollyPhonic, 'academicsMeetings'=>$academicsMeetings, 'timeDisclipineemail'=>$timeDisclipineemail, 'uniformDisclipineemail'=>$uniformDisclipineemail, 'curiculumnShared'=>$curiculumnShared, 'holidaEventlisting'=>$holidaEventlisting, 'sharingAssessmentpapers'=>$sharingAssessmentpapers, 'assessmentSharingemail'=>$assessmentSharingemail, 'PTMscheduledate'=>$PTMscheduledate, 'shadowPuppet'=>$shadowPuppet, 'monthlyEventtraining'=>$monthlyEventtraining, 'summertCampdate'=>$summertCampdate, 'winterCampdate'=>$winterCampdate, 'offerName'=>$offerName, 'offerPlanname'=>$offerPlanname, 'discountAmount'=>$discountAmount, 'finalAmount'=>$finalAmount, 'legalChargesSales'=>$legalChargesSales, 'brSetupinsChargSales'=>$brSetupinsChargSales, 'numInialKitSales'=>$numInialKitSales, 'franchiseTenure'=>$franchiseTenure, 'welComeFolderStatus'=>$welComeFolderStatus, 'welComeFolderDtm'=>$welComeFolderDtm, 'trainingAmount'=>$trainingAmount, 'societyServiceamount'=>$societyServiceamount, 'totalAmount'=>$totalAmount, 'gstAmount'=>$gstAmount, 'totalfranchisegstFund'=>$totalfranchisegstFund, 'legalCharges'=>$legalCharges, 'legalChargesdue'=>$legalChargesdue, 'totalgstCharges'=>$totalgstCharges, 'totalPaidamount'=>$totalPaidamount, 'dueFranchiseamt'=>$dueFranchiseamt, 'kitCharges'=>$kitCharges, 'numinitialKit'=>$numinitialKit, 'totalKitsamt'=>$totalKitsamt, 'kitamtReceived'=>$kitamtReceived, 'dueKitamount'=>$dueKitamount, 'installationDate'=>$installationDate, 'finaltotalamtDue'=>$finaltotalamtDue, 'specialRemark'=>$specialRemark, 'transporttravCharge'=>$transporttravCharge, 'brsetupinstachargReceived'=>$brsetupinstachargReceived, 'brsetupinstachargDue'=>$brsetupinstachargDue, 'travelAmount'=>$travelAmount, 'receivedtravelAmount'=>$receivedtravelAmount, 'duetravelAmount'=>$duetravelAmount, 'transportCharges'=>$transportCharges, 'transportAmtreceived'=>$transportAmtreceived, 'duetransportCharges'=>$duetransportCharges, 'ledgerMarch'=>$ledgerMarch, 'ledgerJune'=>$ledgerJune, 'ledgerSeptember'=>$ledgerSeptember, 'ledgerDecember'=>$ledgerDecember, 'reminderAMCStatus1Dec'=>$reminderAMCStatus1Dec, 'reminderAMCStatus10Dec'=>$reminderAMCStatus10Dec, 'reminderAMCStatus15Dec'=>$reminderAMCStatus15Dec, 'reminderAMCStatus19Dec'=>$reminderAMCStatus19Dec, 'reminderAMCStatus20Dec'=>$reminderAMCStatus20Dec, 'RemarkforAMCmail'=>$RemarkforAMCmail, 'InvoiceAMCClearance'=>$InvoiceAMCClearance, 'PenaltyMailnoncle'=>$PenaltyMailnoncle, 'invoiceNumberAll'=>$invoiceNumberAll, 'upgradeUptoclass'=>$upgradeUptoclass, 'branchStatus'=>$branchStatus, 'brInstallationStatus'=>$brInstallationStatus, 'undertakingAck'=>$undertakingAck, 'optOnlineMarketing'=>$optOnlineMarketing, 'insmatDispatchdate'=>$insmatDispatchdate, 'DetailsReceiptmail'=>$DetailsReceiptmail, 'ConfBrinsScheduledemail'=>$ConfBrinsScheduledemail, 'Materialrecdate'=>$Materialrecdate, 'BrinsScheduleddate'=>$BrinsScheduleddate, 'BrinsScheduledemail'=>$BrinsScheduledemail, 'brInstalationRemark'=>$brInstalationRemark, 'videoFeedbackbr'=>$videoFeedbackbr, 'writtenFeedbackbr'=>$writtenFeedbackbr, 'ShoppinPortSharedDate'=>$ShoppinPortSharedDate, 'ShoppinPortTraining'=>$ShoppinPortTraining, 'ShoppinPortTrainingDate'=>$ShoppinPortTrainingDate, 'ShoppinPortRemark'=>$ShoppinPortRemark,'returnItems'=>$returnItems, 'modeOfDespatch'=>$modeOfDespatch, 'NumOfBoxes'=>$NumOfBoxes, 'PoDNum'=>$PoDNum, 'SpecificGiftOffer'=>$SpecificGiftOffer, 'ConfBrInsOverPhone'=>$ConfBrInsOverPhone, 'shortComming'=>$shortComming, 'solutionShortComming'=>$solutionShortComming, 'customWebsiteLink'=>$customWebsiteLink, 
             




                    'createdBy'=>$this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));
                
                $result = $this->bm->addNewBranches($branchesInfo);
              //print_r($branchesInfo);exit;
                 if ($result > 0) {
                //  Add Legal Documents
                $this->addLegalDocuments($result);

                
    // ✅ Load Notification Model
    $this->load->model('Notification_model');

    $assignedUser = $this->Notification_model->get_assigned_user_by_branch($result);

    if (!empty($assignedUser) && $assignedUser->branchFranchiseAssigned) {
        // ✅ Send Notification to the Assigned User
        $notificationMessage = "<strong>Branches:</strong>A new branch (" . $branchcityName . ") has been assigned to you.";
        $this->Notification_model->add_branch_notification($assignedUser->branchFranchiseAssigned, $notificationMessage, $result);
        
        log_message('error', "✅ DEBUG: Notification Sent to Assigned User - UserID: " . $assignedUser->branchFranchiseAssigned);
    } else {
        log_message('error', "❌ ERROR: No assigned user found for Branch ID - $result");
    }

    $this->session->set_flashdata('success', 'New Branch created successfully');
} else {
    $this->session->set_flashdata('error', 'Branch creation failed');
}
            redirect('branches/branchesListing');
        }
    }
}

    
    /**
     * This function is used load branches edit information
     * @param number $branchesId : Optional : This is branches id
     */
    function edit($branchesId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($branchesId == null)
            {
                redirect('branches/branchesListing');
            }
            $data['users'] = $this->bm->getUser();
            $data['LDusers'] = $this->bm->getLDUser();
            $data['dusers'] = $this->bm->getDUser();
            $data['ACusers'] = $this->bm->getACUser();
            $data['ADMusers'] = $this->bm->getADMUser();
            $data['DISusers'] = $this->bm->getDISUser();
            $data['ATMusers'] = $this->bm->getATMUser();
            $data['MATusers'] = $this->bm->getMATUser();
            $data['DMusers'] = $this->bm->getDMUser();
            $data['TRMusers'] = $this->bm->getTRMUser();
            $data['SMDusers'] = $this->bm->getSMDuser();
             $data['user'] = $this->bm->getAllUserRole();
            $data['branchesInfo'] = $this->bm->getBranchesInfo($branchesId);
          //print_r($data['branchesInfo']);exit;
             /*---Start Legal Documets--*/
            $legaDocumnets = $this->bm->getallLegalDocument($branchesId);
            $data2['legaDocumnets'] = json_decode($legaDocumnets->access);
            $data2['legalDocuments'] = $this->config->item('legalDocuments');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray = $data2['legaDocumnets'] ;
            $secondArray = [];
            foreach ($firstArray as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray[] = (array)$item + ['module' => $module];
            }
            $data2['legaDocumnets'] = $secondArray;

            //Legal PVTL Documents
            $legalDocumentsPvtLtd = $this->bm->getallLegalPvtlDocument($branchesId);
            $data2['legalDocumentsPvtLtd'] = json_decode($legalDocumentsPvtLtd->access);

            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';

            $firstArray1 = $data2['legalDocumentsPvtLtd'];
            $secondArray1 = [];

            foreach ($firstArray1 as $item1) {
                $module = $item1->module;
                unset($item1->module); // Remove the 'module' key from the item
                $secondArray1[] = (array)$item1 + ['module' => $module];
            }

            $data2['legalDocumentsPvtLtd'] = $secondArray1;



            //Legal Society Documents
            $legalDocumentsSociety = $this->bm->getallLegalSocietyDocument($branchesId);
            $data2['legalDocumentsSociety'] = json_decode($legalDocumentsSociety->access);
            // $data2['legalDocumentsSociety'] = $this->config->item('legalDocumentsSociety');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray2 = $data2['legalDocumentsSociety'] ;
            $secondArray2 = [];
            foreach ($firstArray2 as $item2) {
                $module = $item2->module;
                unset($item2->module); // Remove the 'module' key from the item
                $secondArray2[] = (array)$item2 + ['module' => $module];
            }
            $data2['legalDocumentsSociety'] = $secondArray2;

            //Legal Truest Documents
            $legalDocumentsTrust = $this->bm->getallLegalTrustDocument($branchesId);
            $data2['legalDocumentsTrust'] = json_decode($legalDocumentsTrust->access);
            // $data2['legalDocumentsTrust'] = $this->config->item('legalDocumentsTrust');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray3 = $data2['legalDocumentsTrust'] ;
            $secondArray3 = [];
            foreach ($firstArray3 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray3[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsTrust'] = $secondArray3;

            //Legal Partnership Documents
            $legalDocumentsPartnership = $this->bm->getallLegalprDocument($branchesId);
            $data2['legalDocumentsPartnership'] = json_decode($legalDocumentsPartnership->access);
            // $data2['legalDocumentsPartnership'] = $this->config->item('legalDocumentsPartnership');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray4 = $data2['legalDocumentsPartnership'] ;
            $secondArray4 = [];
            foreach ($firstArray4 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray4[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsPartnership'] = $secondArray4;

            //Legal Partnership Documents
            $legalDocumentsHUF = $this->bm->getallLegalHUFDocument($branchesId);
            $data2['legalDocumentsHUF'] = json_decode($legalDocumentsHUF->access);
            // $data2['legalDocumentsHUF'] = $this->config->item('legalDocumentsHUF');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray5 = $data2['legalDocumentsHUF'] ;
            $secondArray5= [];
            foreach ($firstArray5 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray5[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsHUF'] = $secondArray5;

            //Legal Proprietorship Documents
            $legalDocumentsProprietorship = $this->bm->getallLegalPropriDocument($branchesId);
            $data2['legalDocumentsProprietorship'] = json_decode($legalDocumentsProprietorship->access);
            // $data2['legalDocumentsProprietorship'] = $this->config->item('legalDocumentsProprietorship');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray6 = $data2['legalDocumentsProprietorship'] ;
            $secondArray6= [];
            foreach ($firstArray6 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray6[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsProprietorship'] = $secondArray6;

            //Legal Individual Documents
            $legalDocumentsIndividual = $this->bm->getallLegalIndividualDocument($branchesId);
            $data2['legalDocumentsIndividual'] = json_decode($legalDocumentsIndividual->access);
            // $data2['legalDocumentsIndividual'] = $this->config->item('legalDocumentsIndividual');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray7 = $data2['legalDocumentsIndividual'] ;
            $secondArray7= [];
            foreach ($firstArray7 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray7[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsIndividual'] = $secondArray7;
            /*---End Legal Documets--*/
            $mergedData = array_merge($data, $data2);
            
            $this->loadViews("branches/edit", $this->global, $mergedData, NULL);
        }
    }
    
  


    
    function view($branchesId = NULL)
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            if($branchesId == null)
            {
                redirect('branches/branchesListing');
            }
            $data['users'] = $this->bm->getUser();
            $data['LDusers'] = $this->bm->getLDUser();
            $data['dusers'] = $this->bm->getDUser();
            $data['ACusers'] = $this->bm->getACUser();
            $data['ADMusers'] = $this->bm->getADMUser();
            $data['DISusers'] = $this->bm->getDISUser();
            $data['ATMusers'] = $this->bm->getATMUser();
            $data['MATusers'] = $this->bm->getMATUser();
            $data['DMusers'] = $this->bm->getDMUser();
            $data['TRMusers'] = $this->bm->getTRMUser();
            $data['SMDusers'] = $this->bm->getSMDuser();
            $data['user'] = $this->bm->getAllUserRole();
            $data['branchesInfo'] = $this->bm->getBranchesInfo($branchesId);
//print_r($data['branchesInfo']);exit;
            /*---Start Legal Documets--*/
            /*---Start Legal Documets--*/
            $legaDocumnets = $this->bm->getallLegalDocument($branchesId);
            $data2['legaDocumnets'] = json_decode($legaDocumnets->access);
            $data2['legalDocuments'] = $this->config->item('legalDocuments');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray = $data2['legaDocumnets'] ;
            $secondArray = [];
            foreach ($firstArray as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray[] = (array)$item + ['module' => $module];
            }
            $data2['legaDocumnets'] = $secondArray;

            //Legal PVTL Documents
            $legalDocumentsPvtLtd = $this->bm->getallLegalPvtlDocument($branchesId);
            $data2['legalDocumentsPvtLtd'] = json_decode($legalDocumentsPvtLtd->access);

            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';

            $firstArray1 = $data2['legalDocumentsPvtLtd'];
            $secondArray1 = [];

            foreach ($firstArray1 as $item1) {
                $module = $item1->module;
                unset($item1->module); // Remove the 'module' key from the item
                $secondArray1[] = (array)$item1 + ['module' => $module];
            }

            $data2['legalDocumentsPvtLtd'] = $secondArray1;



            //Legal Society Documents
            $legalDocumentsSociety = $this->bm->getallLegalSocietyDocument($branchesId);
            $data2['legalDocumentsSociety'] = json_decode($legalDocumentsSociety->access);
            // $data2['legalDocumentsSociety'] = $this->config->item('legalDocumentsSociety');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray2 = $data2['legalDocumentsSociety'] ;
            $secondArray2 = [];
            foreach ($firstArray2 as $item2) {
                $module = $item2->module;
                unset($item2->module); // Remove the 'module' key from the item
                $secondArray2[] = (array)$item2 + ['module' => $module];
            }
            $data2['legalDocumentsSociety'] = $secondArray2;

            //Legal Truest Documents
            $legalDocumentsTrust = $this->bm->getallLegalTrustDocument($branchesId);
            $data2['legalDocumentsTrust'] = json_decode($legalDocumentsTrust->access);
            // $data2['legalDocumentsTrust'] = $this->config->item('legalDocumentsTrust');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray3 = $data2['legalDocumentsTrust'] ;
            $secondArray3 = [];
            foreach ($firstArray3 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray3[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsTrust'] = $secondArray3;

            //Legal Partnership Documents
            $legalDocumentsPartnership = $this->bm->getallLegalprDocument($branchesId);
            $data2['legalDocumentsPartnership'] = json_decode($legalDocumentsPartnership->access);
            // $data2['legalDocumentsPartnership'] = $this->config->item('legalDocumentsPartnership');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray4 = $data2['legalDocumentsPartnership'] ;
            $secondArray4 = [];
            foreach ($firstArray4 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray4[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsPartnership'] = $secondArray4;

            //Legal Partnership Documents
            $legalDocumentsHUF = $this->bm->getallLegalHUFDocument($branchesId);
            $data2['legalDocumentsHUF'] = json_decode($legalDocumentsHUF->access);
            // $data2['legalDocumentsHUF'] = $this->config->item('legalDocumentsHUF');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray5 = $data2['legalDocumentsHUF'] ;
            $secondArray5= [];
            foreach ($firstArray5 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray5[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsHUF'] = $secondArray5;

            //Legal Proprietorship Documents
            $legalDocumentsProprietorship = $this->bm->getallLegalPropriDocument($branchesId);
            $data2['legalDocumentsProprietorship'] = json_decode($legalDocumentsProprietorship->access);
            // $data2['legalDocumentsProprietorship'] = $this->config->item('legalDocumentsProprietorship');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray6 = $data2['legalDocumentsProprietorship'] ;
            $secondArray6= [];
            foreach ($firstArray6 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray6[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsProprietorship'] = $secondArray6;

            //Legal Individual Documents
            $legalDocumentsIndividual = $this->bm->getallLegalIndividualDocument($branchesId);
            $data2['legalDocumentsIndividual'] = json_decode($legalDocumentsIndividual->access);
            // $data2['legalDocumentsIndividual'] = $this->config->item('legalDocumentsIndividual');
            $this->global['pageTitle'] = 'CodeInsect : Edit Branches';
            $firstArray7 = $data2['legalDocumentsIndividual'] ;
            $secondArray7= [];
            foreach ($firstArray7 as $item) {
                $module = $item->module;
                unset($item->module); // Remove the 'module' key from the item
                $secondArray7[] = (array)$item + ['module' => $module];
            }
            $data2['legalDocumentsIndividual'] = $secondArray7;
            
            $mergedData = array_merge($data, $data2);
            
            $this->loadViews("branches/view", $this->global, $mergedData, NULL);
        }
    }
    /**
     * This function is used to edit the user information
     */
    function editBranches()
    {
        if(!$this->hasUpdateAccess())
        {
            $this->loadThis();
        }
        else
        {
            $this->load->library('form_validation');
            
            $branchesId = $this->input->post('branchesId');
            
            $this->form_validation->set_rules('applicantName','Applicant Name','trim|required|max_length[225]');
            $this->form_validation->set_rules('branchAddress','Address','trim|required|max_length[1024]');
            
            if($this->form_validation->run() == FALSE)
            {
                $this->edit($branchesId);
            }
            else
            {
               /*---Start Legal Documents--*/
$legalDocs = $this->input->post('accesslegalDocumentsIndividual');
if (is_array($legalDocs)) {
    $this->load->config('modules');
    $modules = $this->config->item('legalDocuments');
    
    // Given first array
    $firstArray = $modules;
    // Given second array
    $secondArray = $legalDocs;
    
    // Iterate through the second array and update the first array
    foreach ($secondArray as $module => $status) {
        foreach ($firstArray as &$item) {
            if ($item['module'] === $module) {
                $item[strtolower($module)] = ($status === 'on') ? 1 : 0;
                break;
            }
        }
    }

    $accessMatrix = ['access' => json_encode($firstArray), 'updatedBy' => $this->vendorId, 'updatedDtm' => date('Y-m-d H:i:s')];
    $updated = $this->bm->updateLegalDocumentsIndividual($branchesId, $accessMatrix);
} else {
    // Handle the case when the $legalDocs is not an array or is null
    // You can log an error or set a default value for $legalDocs
    log_message('error', 'Legal documents array is null or not set for individual.');
}

/*---Start Legal Documents for PPF--*/
$legalDocs1 = $this->input->post('accesslegalDocumentsProprietorship');
if (is_array($legalDocs1)) {
    $this->load->config('modules');
    $modules = $this->config->item('legalDocumentsProprietorship');
    
    // Given first array
    $firstArray1 = $modules;
    // Given second array
    $secondArray1 = $legalDocs1;
    
    // Iterate through the second array and update the first array
    foreach ($secondArray1 as $module => $status) {
        foreach ($firstArray1 as &$item11) {
            if ($item11['module'] === $module) {
                $item11[strtolower($module)] = ($status === 'on') ? 1 : 0;
                break;
            }
        }
    }

    $accessMatrix1 = ['access' => json_encode($firstArray1), 'updatedBy' => $this->vendorId, 'updatedDtm' => date('Y-m-d H:i:s')];
    $updated = $this->bm->updateLegalPropriDocuments($branchesId, $accessMatrix1);
} else {
    // Handle the case when the $legalDocs1 is not an array or is null
    // You can log an error or set a default value for $legalDocs1
    log_message('error', 'Legal documents array is null or not set for proprietorship.');
}

/*for huf */

              // Get posted data for checkboxes and set default values if not present
                $legalDocs2 = $this->input->post('accesslegalDocumentsHUF') ?? [];

// Load modules configuration
$this->load->config('modules');
$modules = $this->config->item('legalDocumentsHUF');

// Ensure $modules is an array before proceeding
$firstArray2 = is_array($modules) ? $modules : [];

// Ensure $legalDocs1 is an array to avoid invalid argument errors
$secondArray2 = is_array($legalDocs2) ? $legalDocs2 : [];

// Iterate through the second array and update the first array
foreach ($secondArray2 as $module => $status) {
    foreach ($firstArray2 as &$item1) {
        // Only update if the module name matches and status is defined
        if (isset($item1['module']) && $item1['module'] === $module) {
            $item1[strtolower($module)] = ($status === 'on') ? 1 : 0;
            break;
        }
    }
}

// Prepare the access matrix for database update
$accessMatrix2 = [
    'access' => json_encode($firstArray2), // Convert to JSON format
    'updatedBy' => $this->vendorId,
    'updatedDtm' => date('Y-m-d H:i:s')
];

// Update the database with the modified array
$updated = $this->bm->updateLegalHUFDocuments($branchesId, $accessMatrix2);

/*for partnership */



              // Get posted data for checkboxes and set default values if not present
                $legalDocs3 = $this->input->post('accesslegalDocumentsPartnership') ?? [];
//print_r($legalDocs3);exit;
// Load modules configuration
$this->load->config('modules');
$modules = $this->config->item('legalDocumentsPartnership');

// Ensure $modules is an array before proceeding
$firstArray3 = is_array($modules) ? $modules : [];

// Ensure $legalDocs1 is an array to avoid invalid argument errors
$secondArray3= is_array($legalDocs3) ? $legalDocs3 : [];

// Iterate through the second array and update the first array
foreach ($secondArray3 as $module => $status) {
    foreach ($firstArray3 as &$item2) {
        // Only update if the module name matches and status is defined
        if (isset($item2['module']) && $item2['module'] === $module) {
            $item2[strtolower($module)] = ($status === 'on') ? 1 : 0;
            break;
        }
    }
}

// Prepare the access matrix for database update
$accessMatrix3 = [
    'access' => json_encode($firstArray3), // Convert to JSON format
    'updatedBy' => $this->vendorId,
    'updatedDtm' => date('Y-m-d H:i:s')
];

// Update the database with the modified array
$updated = $this->bm->updateLegalPRTDocuments($branchesId, $accessMatrix3);
//print_r($accessMatrix3);exit;

/*for trust */

              // Get posted data for checkboxes and set default values if not present
                $legalDocs4 = $this->input->post('accesslegalDocumentsTrust') ?? [];
//print_r($legalDocs4);exit;
// Load modules configuration
$this->load->config('modules');
$modules = $this->config->item('legalDocumentsTrust');

// Ensure $modules is an array before proceeding
$firstArray4 = is_array($modules) ? $modules : [];

// Ensure $legalDocs1 is an array to avoid invalid argument errors
$secondArray4= is_array($legalDocs4) ? $legalDocs4 : [];

// Iterate through the second array and update the first array
foreach ($secondArray4 as $module => $status) {
    foreach ($firstArray4 as &$item3) {
        // Only update if the module name matches and status is defined
        if (isset($item3['module']) && $item3['module'] === $module) {
            $item3[strtolower($module)] = ($status === 'on') ? 1 : 0;
            break;
        }
    }
}

// Prepare the access matrix for database update
$accessMatrix4 = [
    'access' => json_encode($firstArray4), // Convert to JSON format
    'updatedBy' => $this->vendorId,
    'updatedDtm' => date('Y-m-d H:i:s')
];

// Update the database with the modified array
$updated = $this->bm->updateLegalTurstDocuments($branchesId, $accessMatrix4);
//print_r($accessMatrix4);exit;

/*for Society */

              // Get posted data for checkboxes and set default values if not present
                $legalDocs5 = $this->input->post('accesslegalDocumentsSociety') ?? [];
//print_r($legalDocs5);exit;
// Load modules configuration
$this->load->config('modules');
$modules = $this->config->item('legalDocumentsSociety');

// Ensure $modules is an array before proceeding
$firstArray5 = is_array($modules) ? $modules : [];

// Ensure $legalDocs1 is an array to avoid invalid argument errors
$secondArray5= is_array($legalDocs5) ? $legalDocs5 : [];

// Iterate through the second array and update the first array
foreach ($secondArray5 as $module => $status) {
    foreach ($firstArray5 as &$item4) {
        // Only update if the module name matches and status is defined
        if (isset($item4['module']) && $item4['module'] === $module) {
            $item4[strtolower($module)] = ($status === 'on') ? 1 : 0;
            break;
        }
    }
}

// Prepare the access matrix for database update
$accessMatrix5 = [
    'access' => json_encode($firstArray5), // Convert to JSON format
    'updatedBy' => $this->vendorId,
    'updatedDtm' => date('Y-m-d H:i:s')
];

// Update the database with the modified array
$updated = $this->bm->updateLegalSocietyDocuments($branchesId, $accessMatrix5);
//print_r($accessMatrix5);exit;
                /*---End Legal Documets--*/
               
                //$applicantName = $this->security->xss_clean($this->input->post('applicantName'));
                //$address = $this->security->xss_clean($this->input->post('address'));
                $applicantName = $this->security->xss_clean($this->input->post('applicantName')?? '');
                $branchAddress = $this->security->xss_clean($this->input->post('branchAddress')?? '');
                $mobile = $this->security->xss_clean($this->input->post('mobile')?? '');
                $branchEmail = $this->security->xss_clean($this->input->post('branchEmail')?? '');
                $branchcityName = $this->security->xss_clean($this->input->post('branchcityName')?? '');
                $branchSalesDoneby = $this->security->xss_clean($this->input->post('branchSalesDoneby')?? '');
                $branchAmountReceived = $this->security->xss_clean($this->input->post('branchAmountReceived')?? '');
                $branchFranchiseAssigned = $this->security->xss_clean($this->input->post('branchFranchiseAssigned')?? '');
                $branchFranchiseAssignedDesigning = $this->security->xss_clean($this->input->post('branchFranchiseAssignedDesigning')?? '');
                $branchFranchiseAssignedLegalDepartment = $this->security->xss_clean($this->input->post('branchFranchiseAssignedLegalDepartment')?? '');
                $branchFrAssignedAccountsDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedAccountsDepartment')?? '');
                $branchFrAssignedDispatchDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedDispatchDepartment')?? '');
                $branchFrAssignedAdmintrainingDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedAdmintrainingDepartment')?? '');
                $branchFrAssignedAdmissionDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedAdmissionDepartment')?? '');
                $branchFrAssignedMaterialDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedMaterialDepartment')?? '');
                $branchFrAssignedDigitalDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedDigitalDepartment')?? '');
                $branchFrAssignedTrainingDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedTrainingDepartment')?? '');
                $branchFrAssignedSocialmediaDepartment = $this->security->xss_clean($this->input->post('branchFrAssignedSocialmediaDepartment')?? '');
                $branchState = $this->security->xss_clean($this->input->post('branchState')?? '');
                $permanentAddress = $this->security->xss_clean($this->input->post('permanentAddress')?? '');
                $franchiseNumber = $this->security->xss_clean($this->input->post('franchiseNumber'));
                $franchiseName = $this->security->xss_clean($this->input->post('franchiseName')?? '');
                $typeBranch = $this->security->xss_clean($this->input->post('typeBranch')?? '');
                $currentStatus = $this->security->xss_clean($this->input->post('currentStatus')?? ''?? '');
                $bookingDate = $this->security->xss_clean($this->input->post('bookingDate')?? '');
                $licenseNumber = $this->security->xss_clean($this->input->post('licenseNumber')?? '');
                $licenseSharedon = $this->security->xss_clean($this->input->post('licenseSharedon')?? '');
                $validFromDate = $this->security->xss_clean($this->input->post('validFromDate')?? '');
                $validTillDate = $this->security->xss_clean($this->input->post('validTillDate')?? '');
                $branchLocation = $this->security->xss_clean($this->input->post('branchLocation')?? '');
                $adminName = $this->security->xss_clean($this->input->post('adminName')?? '');
                $adminContactNum = $this->security->xss_clean($this->input->post('adminContactNum')?? '');
                $additionalNumber = $this->security->xss_clean($this->input->post('additionalNumber')?? '');
                $officialEmailID = $this->security->xss_clean($this->input->post('officialEmailID')?? '');
                $personalEmailId = $this->security->xss_clean($this->input->post('personalEmailId')?? '');
                /*----Start-Desing-Section---*/
                $biometricInstalled = $this->security->xss_clean($this->input->post('biometricInstalled')?? '');
                $biometricRemark = $this->security->xss_clean($this->input->post('biometricRemark')?? '');
                $biometricInstalledDate = $this->security->xss_clean($this->input->post('biometricInstalledDate')?? '');
                $camaraInstalled = $this->security->xss_clean($this->input->post('camaraInstalled')?? '');
                $camaraRemark = $this->security->xss_clean($this->input->post('camaraRemark')?? '');
                $camaraInstalledDate = $this->security->xss_clean($this->input->post('camaraInstalledDate')?? '');
                $eduMetaAppTraining = $this->security->xss_clean($this->input->post('eduMetaAppTraining')?? '');
                $AppTrainingRemark = $this->security->xss_clean($this->input->post('AppTrainingRemark')?? '');
                $AppTrainingRemarkDate = $this->security->xss_clean($this->input->post('AppTrainingRemarkDate'));
                $congratulationsImg = $this->security->xss_clean($this->input->post('congratulationsImg')?? '');
                $brimguploadedFBStatus = $this->security->xss_clean($this->input->post('brimguploadedFBStatus')?? '');
                $brimguploadedFBDate = $this->security->xss_clean($this->input->post('brimguploadedFBDate')?? '');
                $brimguploadedInstaStatus = $this->security->xss_clean($this->input->post('brimguploadedInstaStatus')?? '');
                $brimguploadedInstaDate = $this->security->xss_clean($this->input->post('brimguploadedInstaDate')?? '');
                $admissionOpenimgStatus = $this->security->xss_clean($this->input->post('admissionOpenimgStatus')?? '');
                $staffHiringimgStatus = $this->security->xss_clean($this->input->post('staffHiringimgStatus')?? ''?? ''?? '');
                $newsletterMarch = $this->security->xss_clean($this->input->post('newsletterMarch')?? ''?? '');
                $newsletterJune = $this->security->xss_clean($this->input->post('newsletterJune')?? ''?? '');
                $newsletterSeptember = $this->security->xss_clean($this->input->post('newsletterSeptember')?? ''?? '');
                $newsletterDecember = $this->security->xss_clean($this->input->post('newsletterDecember')?? ''?? '');
                $OBirthDayImgStatus = $this->security->xss_clean($this->input->post('OBirthDayImgStatus')?? ''?? '');
                $OBirthDayImgSharedDtm = $this->security->xss_clean($this->input->post('OBirthDayImgSharedDtm')?? '');
                $OwnerAnnImgStatus = $this->security->xss_clean($this->input->post('OwnerAnnImgStatus')?? '');
                $OwnerAnnImgSharedDtm = $this->security->xss_clean($this->input->post('OwnerAnnImgSharedDtm')?? '');
                /*----End-Desing-Section---*/
                /*---Start-Digital--*/
                $facebookPageStatus = $this->security->xss_clean($this->input->post('facebookPageStatus')?? '');
                $facebookPageLink = $this->security->xss_clean($this->input->post('facebookPageLink')?? '');
                $facebookPageRemark = $this->security->xss_clean($this->input->post('facebookPageRemark')?? '');
                $googleMapLoc = $this->security->xss_clean($this->input->post('googleMapLoc')?? '');
                $googleMapLocLink = $this->security->xss_clean($this->input->post('googleMapLocLink')?? '');
                $googleMapLocRemark = $this->security->xss_clean($this->input->post('googleMapLocRemark')?? '');
                $instagramPageStatus = $this->security->xss_clean($this->input->post('instagramPageStatus')?? '')?? '';
                $instagramPageID = $this->security->xss_clean($this->input->post('instagramPageID')?? '');
                $instagramPageRemark = $this->security->xss_clean($this->input->post('instagramPageRemark')?? '');
                $jdPageStatus = $this->security->xss_clean($this->input->post('jdPageStatus')?? '');
                $jdPageID = $this->security->xss_clean($this->input->post('jdPageID')?? '');
                $jdPageRemark = $this->security->xss_clean($this->input->post('jdPageRemark')?? '');
                $tweetPageStatus = $this->security->xss_clean($this->input->post('tweetPageStatus')?? '');
                $tweetPageID = $this->security->xss_clean($this->input->post('tweetPageID')?? '');
                $tweetPageRemark = $this->security->xss_clean($this->input->post('tweetPageRemark')?? '');
                $digiMarkCost = $this->security->xss_clean($this->input->post('digiMarkCost')?? '');
                $digiMarkStartDtm = $this->security->xss_clean($this->input->post('digiMarkStartDtm')?? '');
                $digiMarkEndDtm = $this->security->xss_clean($this->input->post('digiMarkEndDtm')?? '');
                $digiMarkReamrk = $this->security->xss_clean($this->input->post('digiMarkReamrk')?? '');
                $insfeedvideoUplodFB = $this->security->xss_clean($this->input->post('insfeedvideoUplodFB')?? '');
                $insfeedvideoUplodYoutube = $this->security->xss_clean($this->input->post('insfeedvideoUplodYoutube')?? '');
                $insfeedvideoUplodInsta = $this->security->xss_clean($this->input->post('insfeedvideoUplodInsta')?? '');
                /*---End-Digital--*/
                $branchLocAddressPremise = $this->security->xss_clean($this->input->post('branchLocAddressPremise')?? '');
                $addOfFranchise = $this->security->xss_clean($this->input->post('addOfFranchise')?? '');
                $gstNumber = $this->security->xss_clean($this->input->post('gstNumber')?? '');
                $undertakingCommitmentSupport = $this->security->xss_clean($this->input->post('undertakingCommitmentSupport')?? '');
                $amcAmount = $this->security->xss_clean($this->input->post('amcAmount')?? '');
                $invoiceNumber = $this->security->xss_clean($this->input->post('invoiceNumber')?? '');
                $agreementTenure = $this->security->xss_clean($this->input->post('agreementTenure')?? '');
                $salesExecutive = $this->security->xss_clean($this->input->post('salesExecutive')?? '');
                $salesTeamlead = $this->security->xss_clean($this->input->post('salesTeamlead')?? '');
                $Manual1 = $this->security->xss_clean($this->input->post('Manual1')?? '');
                $Manual2 = $this->security->xss_clean($this->input->post('Manual2')?? '');
                $Manual3 = $this->security->xss_clean($this->input->post('Manual3')?? '');
                $Reference = $this->security->xss_clean($this->input->post('Reference')?? '');
                $installationTentativeDate = $this->security->xss_clean($this->input->post('installationTentativeDate')?? '');
                $formsDocumentsCompleted = $this->security->xss_clean($this->input->post('formsDocumentsCompleted')?? '');
                $setUpInstallation = $this->security->xss_clean($this->input->post('setUpInstallation')?? '');
                $branchAnniversaryDate = $this->security->xss_clean($this->input->post('branchAnniversaryDate')?? '');
                $admissionCracked = $this->security->xss_clean($this->input->post('admissionCracked')?? '');
                $teacherRecruitment = $this->security->xss_clean($this->input->post('teacherRecruitment')?? '');
                $pgDecidedFee = $this->security->xss_clean($this->input->post('pgDecidedFee'));
                $nurseryDecidedFee = $this->security->xss_clean($this->input->post('nurseryDecidedFee')?? '');
                $KG1DecidedFee = $this->security->xss_clean($this->input->post('KG1DecidedFee')?? '');
                $KG2DecidedFee = $this->security->xss_clean($this->input->post('KG2DecidedFee')?? '');
                $feeSharedStatus = $this->security->xss_clean($this->input->post('feeSharedStatus')?? '');
                $feesRemark = $this->security->xss_clean($this->input->post('feesRemark')?? '');
                $addmissionPG = $this->security->xss_clean($this->input->post('addmissionPG')?? '');
                $addmissionNursary = $this->security->xss_clean($this->input->post('addmissionNursary')?? '');
                $addmissionKg1 = $this->security->xss_clean($this->input->post('addmissionKg1')?? '');
                $addmissionKg2 = $this->security->xss_clean($this->input->post('addmissionKg2')?? '');
                $addmission1st = $this->security->xss_clean($this->input->post('addmission1st')?? '');
                $addmission2nd = $this->security->xss_clean($this->input->post('addmission2nd')?? '');
                $totalAddmission = $this->security->xss_clean($this->input->post('totalAddmission')?? '');
                $addmissionCounselor = $this->security->xss_clean($this->input->post('addmissionCounselor')?? '');
                $lastDiscussaddmission = $this->security->xss_clean($this->input->post('lastDiscussaddmission')?? '');
                $addmissionSheetlink = $this->security->xss_clean($this->input->post('addmissionSheetlink')?? '');
                $dateexlSheetshared = $this->security->xss_clean($this->input->post('dateexlSheetshared')?? '');
                $lastInteractiondate = $this->security->xss_clean($this->input->post('lastInteractiondate')?? '');
                $lastDiscussionby = $this->security->xss_clean($this->input->post('lastDiscussionby')?? '');
                $lastInteractioncomment = $this->security->xss_clean($this->input->post('lastInteractioncomment')?? '');
                $agreementDraftdate = $this->security->xss_clean($this->input->post('agreementDraftdate')?? '');
                $branchLandline  = $this->security->xss_clean($this->input->post('branchLandline')?? '');
                $additionalName = $this->security->xss_clean($this->input->post('additionalName')?? '');
                $finalPaydeadline = $this->security->xss_clean($this->input->post('finalPaydeadline')?? '');
                $BranchSpecialNoteSales = $this->security->xss_clean($this->input->post('BranchSpecialNoteSales')?? '');
                $completeFranchiseAmt = $this->security->xss_clean($this->input->post('completeFranchiseAmt')?? '');
                $confirmationAmt33kGST = $this->security->xss_clean($this->input->post('confirmationAmt33kGST')?? '');
                $happinessLevelbranch = $this->security->xss_clean($this->input->post('happinessLevelbranch')?? '');
                $DesignsPromotional = $this->security->xss_clean($this->input->post('DesignsPromotional')?? '');
                $DesignsPromotionalRemark = $this->security->xss_clean($this->input->post('DesignsPromotionalRemark')?? '');
                $BranchSpecialNote = $this->security->xss_clean($this->input->post('BranchSpecialNote')?? '');
                $OwnerAnniversery = $this->security->xss_clean($this->input->post('OwnerAnniversery')?? '');
                $welcomeCall = $this->security->xss_clean($this->input->post('welcomeCall')?? '');
                $welcomeMail = $this->security->xss_clean($this->input->post('welcomeMail')?? '');
                $whatsappGroup = $this->security->xss_clean($this->input->post('whatsappGroup')?? '');
                $whatsappGroupRemark = $this->security->xss_clean($this->input->post('whatsappGroupRemark')?? '');
                $whatsappGroupdate = $this->security->xss_clean($this->input->post('whatsappGroupdate')?? '');
                $interactionMeeting = $this->security->xss_clean($this->input->post('interactionMeeting')?? '');
                $interactionMeetingRemark = $this->security->xss_clean($this->input->post('interactionMeetingRemark')?? '');
                $undertakingCommitment = $this->security->xss_clean($this->input->post('undertakingCommitment')?? '');
                $onboardingForm = $this->security->xss_clean($this->input->post('onboardingForm')?? '');
                $onboardingFormReceived = $this->security->xss_clean($this->input->post('onboardingFormReceived')?? '');
                $onboardingFormRemark = $this->security->xss_clean($this->input->post('onboardingFormRemark')?? '');
                $installationRequirementmail = $this->security->xss_clean($this->input->post('installationRequirementmail')?? '');
                $installationRequirementmailRemark = $this->security->xss_clean($this->input->post('installationRequirementmailRemark')?? '');   
                $finalAgreementShared = $this->security->xss_clean($this->input->post('finalAgreementShared')?? '');
                $agreementDraftReceiveddate = $this->security->xss_clean($this->input->post('agreementDraftReceiveddate')?? '');
                $compFileSubmit = $this->security->xss_clean($this->input->post('compFileSubmit')?? '');
                $fileCLoserDate = $this->security->xss_clean($this->input->post('fileCLoserDate')?? '');
                $branchStatusRemark = $this->security->xss_clean($this->input->post('branchStatusRemark')?? '');
                $officialemailshared = $this->security->xss_clean($this->input->post('officialemailshared')?? '');
               
                $inaugurationDate = $this->security->xss_clean($this->input->post('inaugurationDate')?? '');
                $classroomDecoration = $this->security->xss_clean($this->input->post('classroomDecoration')?? '');
                $movieClub = $this->security->xss_clean($this->input->post('movieClub')?? '');
                $referEarn = $this->security->xss_clean($this->input->post('referEarn')?? '');
                $teacherInteraction = $this->security->xss_clean($this->input->post('teacherInteraction')?? '');
                $teacherInterview = $this->security->xss_clean($this->input->post('teacherInterview')?? '');
                $pongalWorkshop = $this->security->xss_clean($this->input->post('pongalWorkshop')?? '');
                $sankrantiWorkshop = $this->security->xss_clean($this->input->post('sankrantiWorkshop')?? '');
                $republicDayWorkshop = $this->security->xss_clean($this->input->post('republicDayWorkshop')?? '');
                $bridgeCourseCounselling = $this->security->xss_clean($this->input->post('bridgeCourseCounselling')?? '');
                $bulletinBoard = $this->security->xss_clean($this->input->post('bulletinBoard')?? '');
                $bridgeCourse = $this->security->xss_clean($this->input->post('bridgeCourse')?? '');
                $settlersProgram = $this->security->xss_clean($this->input->post('settlersProgram')?? '');
                $jollyPhonic = $this->security->xss_clean($this->input->post('jollyPhonic')?? '');
                $academicsMeetings = $this->security->xss_clean($this->input->post('academicsMeetings')?? '');
                $timeDisclipineemail = $this->security->xss_clean($this->input->post('timeDisclipineemail')?? '');
                $uniformDisclipineemail = $this->security->xss_clean($this->input->post('uniformDisclipineemail')?? '');
                $curiculumnShared = $this->security->xss_clean($this->input->post('curiculumnShared')?? '');
                $holidaEventlisting = $this->security->xss_clean($this->input->post('holidaEventlisting')?? '');
                $sharingAssessmentpapers = $this->security->xss_clean($this->input->post('sharingAssessmentpapers')?? '');
                $assessmentSharingemail = $this->security->xss_clean($this->input->post('assessmentSharingemail')?? '');
                $PTMscheduledate = $this->security->xss_clean($this->input->post('PTMscheduledate')?? '');
                $shadowPuppet = $this->security->xss_clean($this->input->post('shadowPuppet')?? '');
                $monthlyEventtraining = $this->security->xss_clean($this->input->post('monthlyEventtraining')?? '');
                $summertCampdate = $this->security->xss_clean($this->input->post('summertCampdate')?? '');
                $winterCampdate = $this->security->xss_clean($this->input->post('winterCampdate')?? '');
                 $trainingcat = $this->security->xss_clean($this->input->post('trainingcat')?? '');
                $offerName = $this->security->xss_clean($this->input->post('offerName')?? '');
                $offerPlanname = $this->security->xss_clean($this->input->post('offerPlanname')?? '');
                $discountAmount = $this->security->xss_clean($this->input->post('discountAmount')?? '');
                $finalAmount = $this->security->xss_clean($this->input->post('finalAmount')?? '');
                /*Added Additional Field by Sales-04-Jul-2024 */
                $legalChargesSales = $this->security->xss_clean($this->input->post('legalChargesSales')?? '');
                $brSetupinsChargSales = $this->security->xss_clean($this->input->post('brSetupinsChargSales')?? '');
                $numInialKitSales = $this->security->xss_clean($this->input->post('numInialKitSales')?? '');
                $franchiseTenure = $this->security->xss_clean($this->input->post('franchiseTenure')?? '');
                /*End-Added Additional Field by Sales-04-Jul-2024 */
                $welComeFolderStatus = $this->security->xss_clean($this->input->post('welComeFolderStatus')?? '');
                $welComeFolderDtm = $this->security->xss_clean($this->input->post('welComeFolderDtm')?? '');
                $trainingAmount = $this->security->xss_clean($this->input->post('trainingAmount')?? '');
                $societyServiceamount = $this->security->xss_clean($this->input->post('societyServiceamount')?? '');
                $totalAmount = $this->security->xss_clean($this->input->post('totalAmount')?? '');
                $gstAmount = $this->security->xss_clean($this->input->post('gstAmount')?? '');
                $totalfranchisegstFund = $this->security->xss_clean($this->input->post('totalfranchisegstFund')?? '');
                $legalCharges = $this->security->xss_clean($this->input->post('legalCharges')?? '');
                $legalChargesdue = $this->security->xss_clean($this->input->post('legalChargesdue')?? '');
                $totalgstCharges = $this->security->xss_clean($this->input->post('totalgstCharges')?? '');
                $totalPaidamount = $this->security->xss_clean($this->input->post('totalPaidamount')?? '');
                $dueFranchiseamt = $this->security->xss_clean($this->input->post('dueFranchiseamt')?? '');
                $kitCharges = $this->security->xss_clean($this->input->post('kitCharges')?? '');
                $numinitialKit = $this->security->xss_clean($this->input->post('numinitialKit')?? '');
                $totalKitsamt = $this->security->xss_clean($this->input->post('totalKitsamt')?? '');
                $kitamtReceived = $this->security->xss_clean($this->input->post('kitamtReceived')?? '');
                $dueKitamount = $this->security->xss_clean($this->input->post('dueKitamount')?? '');
                $installationDate = $this->security->xss_clean($this->input->post('installationDate')?? '');
                $finaltotalamtDue = $this->security->xss_clean($this->input->post('finaltotalamtDue')?? '');
                $specialRemark = $this->security->xss_clean($this->input->post('specialRemark')?? '');
                $transporttravCharge = $this->security->xss_clean($this->input->post('transporttravCharge')?? '');
                $brsetupinstachargReceived = $this->security->xss_clean($this->input->post('brsetupinstachargReceived')?? '');
                $brsetupinstachargDue = $this->security->xss_clean($this->input->post('brsetupinstachargDue')?? '');
                $travelAmount = $this->security->xss_clean($this->input->post('travelAmount')?? '');
                $receivedtravelAmount = $this->security->xss_clean($this->input->post('receivedtravelAmount')?? '');
                $duetravelAmount = $this->security->xss_clean($this->input->post('duetravelAmount')?? '');
                $transportCharges = $this->security->xss_clean($this->input->post('transportCharges')?? '');
                $transportAmtreceived = $this->security->xss_clean($this->input->post('transportAmtreceived')?? '');
                $duetransportCharges = $this->security->xss_clean($this->input->post('duetransportCharges')?? '');
                /*---New-Accounts--*/
                $ledgerMarch = $this->security->xss_clean($this->input->post('ledgerMarch')?? '');
                $ledgerJune = $this->security->xss_clean($this->input->post('ledgerJune')?? '');
                $ledgerSeptember = $this->security->xss_clean($this->input->post('ledgerSeptember')?? '');
                $ledgerDecember = $this->security->xss_clean($this->input->post('ledgerDecember')?? '');
                $reminderAMCStatus1Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus1Dec')?? '');
                $reminderAMCStatus10Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus10Dec')?? '');
                $reminderAMCStatus15Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus15Dec')?? '');
                $reminderAMCStatus19Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus19Dec')?? '');
                $reminderAMCStatus20Dec = $this->security->xss_clean($this->input->post('reminderAMCStatus20Dec')?? '');
                $RemarkforAMCmail = $this->security->xss_clean($this->input->post('RemarkforAMCmail')?? '');
                $InvoiceAMCClearance = $this->security->xss_clean($this->input->post('InvoiceAMCClearance')?? '');
                $PenaltyMailnoncle = $this->security->xss_clean($this->input->post('PenaltyMailnoncle')?? '');
                $invoiceNumberAll = $this->security->xss_clean($this->input->post('invoiceNumberAll')?? '');
                /*---End-New-Accounts--*/
                $upgradeUptoclass = $this->security->xss_clean($this->input->post('upgradeUptoclass')?? '');
                $branchStatus = $this->security->xss_clean($this->input->post('branchStatus')?? '');
                $brInstallationStatus = $this->security->xss_clean($this->input->post('brInstallationStatus')?? '');
                $undertakingAck = $this->security->xss_clean($this->input->post('undertakingAck')?? '');
                $optOnlineMarketing = $this->security->xss_clean($this->input->post('optOnlineMarketing')?? '');
                /*---Despatch--*/
                $insmatDispatchdate = $this->security->xss_clean($this->input->post('insmatDispatchdate')?? '');
                $DetailsReceiptmail = $this->security->xss_clean($this->input->post('DetailsReceiptmail')?? '');
                $ConfBrinsScheduledemail = $this->security->xss_clean($this->input->post('ConfBrinsScheduledemail')?? '');
                $Materialrecdate = $this->security->xss_clean($this->input->post('Materialrecdate')?? '');
                $BrinsScheduleddate = $this->security->xss_clean($this->input->post('BrinsScheduleddate')?? '');
                $BrinsScheduledemail = $this->security->xss_clean($this->input->post('BrinsScheduledemail')?? '');
                $brInstalationRemark = $this->security->xss_clean($this->input->post('brInstalationRemark')?? '');
                $videoFeedbackbr = $this->security->xss_clean($this->input->post('videoFeedbackbr')?? '');
                $writtenFeedbackbr = $this->security->xss_clean($this->input->post('writtenFeedbackbr')?? '');
                $ShoppinPortSharedDate = $this->security->xss_clean($this->input->post('ShoppinPortSharedDate')?? '');
                $ShoppinPortTraining = $this->security->xss_clean($this->input->post('ShoppinPortTraining')?? '');
                $ShoppinPortTrainingDate = $this->security->xss_clean($this->input->post('ShoppinPortTrainingDate')?? '');
                $ShoppinPortRemark = $this->security->xss_clean($this->input->post('ShoppinPortRemark')?? '');
                $returnItems = $this->security->xss_clean($this->input->post('returnItems')?? '');
                $modeOfDespatch = $this->security->xss_clean($this->input->post('modeOfDespatch')?? '');
                $NumOfBoxes = $this->security->xss_clean($this->input->post('NumOfBoxes')?? '');
                $PoDNum = $this->security->xss_clean($this->input->post('PoDNum')?? '');
                $SpecificGiftOffer = $this->security->xss_clean($this->input->post('SpecificGiftOffer')?? '');
                $ConfBrInsOverPhone = $this->security->xss_clean($this->input->post('ConfBrInsOverPhone')?? '');
                $shortComming = $this->security->xss_clean($this->input->post('shortComming')?? '');
                $solutionShortComming = $this->security->xss_clean($this->input->post('solutionShortComming')?? '');
               $customWebsiteLink = $this->security->xss_clean($this->input->post('customWebsiteLink')?? '');
              $LedgerMonthDrop = $this->security->xss_clean($this->input->post('LedgerMonthDrop')?? '');
                $LedgerYear = $this->security->xss_clean($this->input->post('LedgerYear')?? ''); 

                 $IntroductionDate = $this->security->xss_clean($this->input->post('IntroductionDate')?? '');
                $Pre_marketingDate = $this->security->xss_clean($this->input->post('Pre_marketingDate')?? '');
                $Admin_OrientationDate = $this->security->xss_clean($this->input->post('Admin_OrientationDate')?? '');
                $Inauguration_Refer_and_EarnDate = $this->security->xss_clean($this->input->post('Inauguration_Refer_and_EarnDate')?? '');
                
                $Classroom_decoration = $this->security->xss_clean($this->input->post('Classroom_decoration')?? '');
                $Movie_clubDate = $this->security->xss_clean($this->input->post('Movie_clubDate')?? '');
                $Fee_structureDate = $this->security->xss_clean($this->input->post('Fee_structureDate')?? '');
                $Day_careDate = $this->security->xss_clean($this->input->post('Day_careDate')?? '');
                $ToddlerDate = $this->security->xss_clean($this->input->post('ToddlerDate')?? '');

                $pG_April_JuneDate = $this->security->xss_clean($this->input->post('pG_April_JuneDate')?? '');
                $pG_JulyDate = $this->security->xss_clean($this->input->post('pG_JulyDate')?? '');
                 $pG_AugustDate = $this->security->xss_clean($this->input->post('pG_AugustDate')?? '');
                $pG_SeptemberDate = $this->security->xss_clean($this->input->post('pG_SeptemberDate')?? '');
                $pG_OctoberDate = $this->security->xss_clean($this->input->post('pG_OctoberDate')?? '');
                $pG_NovemberDate = $this->security->xss_clean($this->input->post('pG_NovemberDate')?? '');
                $pG_DecemberDate = $this->security->xss_clean($this->input->post('pG_DecemberDate')?? '');
                $pG_JanuaryDate = $this->security->xss_clean($this->input->post('pG_JanuaryDate')?? '');
                $pG_FebruaryDate = $this->security->xss_clean($this->input->post('pG_FebruaryDate')?? '');
                 $pG_MarchDate = $this->security->xss_clean($this->input->post('pG_MarchDate')?? '');

                  $NurseryBook_1_Date = $this->security->xss_clean($this->input->post('NurseryBook_1_Date')?? '');
                $NurseryBook_2_Date = $this->security->xss_clean($this->input->post('NurseryBook_2_Date')?? '');
                 $NurseryBook_3_Date = $this->security->xss_clean($this->input->post('NurseryBook_3_Date')?? '');
                $NurseryBook_4_Date = $this->security->xss_clean($this->input->post('NurseryBook_4_Date')?? '');
                
                $NurseryBook_5_Date = $this->security->xss_clean($this->input->post('NurseryBook_5_Date')?? '');
                $NurseryBook_6_Date = $this->security->xss_clean($this->input->post('NurseryBook_6_Date')?? '');
                $NurseryBook_7_Date = $this->security->xss_clean($this->input->post('NurseryBook_7_Date')?? '');
                $NurseryBook_8_Date = $this->security->xss_clean($this->input->post('NurseryBook_8_Date')?? '');
                $NurseryBook_9_Date = $this->security->xss_clean($this->input->post('NurseryBook_9_Date')?? '');

                //KG
                $KG1Book_1_Date = $this->security->xss_clean($this->input->post('KG1Book_1_Date')?? '');
                $KG1Book_2_Date = $this->security->xss_clean($this->input->post('KG1Book_2_Date')?? '');
                $KG1Book_3_Date = $this->security->xss_clean($this->input->post('KG1Book_3_Date')?? '');
                $KG1Book_4_Date = $this->security->xss_clean($this->input->post('KG1Book_4_Date')?? '');
                $KG1Book_5_Date = $this->security->xss_clean($this->input->post('KG1Book_5_Date')?? '');
                $KG1Book_6_Date = $this->security->xss_clean($this->input->post('KG1Book_6_Date')?? '');
                $KG1Book_7_Date = $this->security->xss_clean($this->input->post('KG1Book_7_Date')?? '');
                $KG1Book_8_Date = $this->security->xss_clean($this->input->post('KG1Book_8_Date')?? '');
                $KG1Book_9_Date = $this->security->xss_clean($this->input->post('KG1Book_9_Date')?? '');

                //KG2
                $KG2Book_1_Date = $this->security->xss_clean($this->input->post('KG2Book_1_Date')?? '');
                $KG2Book_2_Date = $this->security->xss_clean($this->input->post('KG2Book_2_Date')?? '');
                $KG2Book_3_Date = $this->security->xss_clean($this->input->post('KG2Book_3_Date')?? '');
                $KG2Book_4_Date = $this->security->xss_clean($this->input->post('KG2Book_4_Date')?? '');
                $KG2Book_5_Date = $this->security->xss_clean($this->input->post('KG2Book_5_Date')?? '');
                $KG2Book_6_Date = $this->security->xss_clean($this->input->post('KG2Book_6_Date')?? '');
                $KG2Book_7_Date = $this->security->xss_clean($this->input->post('KG2Book_7_Date')?? '');
                $KG2Book_8_Date = $this->security->xss_clean($this->input->post('KG2Book_8_Date')?? '');
                $KG2Book_9_Date = $this->security->xss_clean($this->input->post('KG2Book_9_Date')?? '');

                 //Event
                $eventCelebration_April_JuneDate = $this->security->xss_clean($this->input->post('eventCelebration_April_JuneDate')?? '');
                $eventCelebration_JulyDate = $this->security->xss_clean($this->input->post('eventCelebration_JulyDate')?? '');
                 $eventCelebration_AugustDate = $this->security->xss_clean($this->input->post('eventCelebration_AugustDate')?? '');
                $eventCelebration_SeptemberDate = $this->security->xss_clean($this->input->post('eventCelebration_SeptemberDate')?? '');
                $eventCelebration_OctoberDate = $this->security->xss_clean($this->input->post('eventCelebration_OctoberDate')?? '');
                $eventCelebration_NovemberDate = $this->security->xss_clean($this->input->post('eventCelebration_NovemberDate')?? '');
                $eventCelebration_DecemberDate = $this->security->xss_clean($this->input->post('eventCelebration_DecemberDate')?? '');
                $eventCelebration_JanuaryDate = $this->security->xss_clean($this->input->post('eventCelebration_JanuaryDate')?? '');
                $eventCelebration_FebruaryDate = $this->security->xss_clean($this->input->post('eventCelebration_FebruaryDate')?? '');
                $eventCelebration_MarchDate = $this->security->xss_clean($this->input->post('eventCelebration_MarchDate')?? '');


                //WorkSHop
                $Workshop_1 = $this->security->xss_clean($this->input->post('Workshop_1')?? '');
                $Workshop_2 = $this->security->xss_clean($this->input->post('Workshop_2')?? '');
                $Workshop_3 = $this->security->xss_clean($this->input->post('Workshop_3')?? '');
                $Workshop_4 = $this->security->xss_clean($this->input->post('Workshop_4')?? '');
                $Workshop_5 = $this->security->xss_clean($this->input->post('Workshop_5')?? '');
                $Workshop_6 = $this->security->xss_clean($this->input->post('Workshop_6')?? '');
                $Workshop_7 = $this->security->xss_clean($this->input->post('Workshop_7')?? '');

                //others
                $others_Settlers_program_Date = $this->security->xss_clean($this->input->post('others_Settlers_program_Date')?? '');
                $others_Circle_time_Date = $this->security->xss_clean($this->input->post('others_Circle_time_Date')?? '');
                $others_Interview_Date = $this->security->xss_clean($this->input->post('others_Interview_Date')?? '');
                $others_Academic_Overview_Date = $this->security->xss_clean($this->input->post('others_Academic_Overview_Date')?? '');
                $others_Teacher_interaction_Date = $this->security->xss_clean($this->input->post('others_Teacher_interaction_Date')?? '');
                $others_Assessment_Date = $this->security->xss_clean($this->input->post('others_Assessment_Date')?? '');
                $others_PTM_Date = $this->security->xss_clean($this->input->post('others_PTM_Date')?? '');
                $others_Summer_camp_Date = $this->security->xss_clean($this->input->post('others_Summer_camp_Date')?? '');
                $others_Winter_Camp_Date = $this->security->xss_clean($this->input->post('others_Winter_Camp_Date')?? '');
                $others_Aayam_Date = $this->security->xss_clean($this->input->post('others_Aayam_Date')?? '');
                $others_Sports_day_Date = $this->security->xss_clean($this->input->post('others_Sports_day_Date')?? ''); 
                $others_midterm_session = $this->security->xss_clean($this->input->post('others_midterm_session')?? '');
                $others_bridgecourse = $this->security->xss_clean($this->input->post('others_bridgecourse')?? '');
                if (!empty($applicantName)) {
                    $branchesInfo['applicantName'] =$applicantName;
                }
                if (!empty($mobile)) {
                    $branchesInfo['mobile'] =$mobile;
                }
                if (!empty($branchEmail)) {
                    $branchesInfo['branchEmail'] =$branchEmail;
                }
                if (!empty($branchcityName)) {
                    $branchesInfo['branchcityName'] =$branchcityName;
                }
                if (!empty($branchState)) {
                    $branchesInfo['branchState'] =$branchState;
                }
                if (!empty($branchSalesDoneby)) {
                    $branchesInfo['branchSalesDoneby'] =$branchSalesDoneby;
                }
                if (!empty($branchAmountReceived)) {
                    $branchesInfo['branchAmountReceived'] =$branchAmountReceived;
                }
                if (!empty($branchFranchiseAssigned)) {
                    $branchesInfo['branchFranchiseAssigned'] =$branchFranchiseAssigned;
                }
                if (!empty($branchFranchiseAssignedDesigning)) {
                    $branchesInfo['branchFranchiseAssignedDesigning'] =$branchFranchiseAssignedDesigning;
                }
                if (!empty($branchFranchiseAssignedLegalDepartment)) {
                    $branchesInfo['branchFranchiseAssignedLegalDepartment'] =$branchFranchiseAssignedLegalDepartment;
                }
                if (!empty($branchFrAssignedAccountsDepartment)) {
                    $branchesInfo['branchFrAssignedAccountsDepartment'] =$branchFrAssignedAccountsDepartment;
                }
                if (!empty($branchFrAssignedDispatchDepartment)) {
                    $branchesInfo['branchFrAssignedDispatchDepartment'] =$branchFrAssignedDispatchDepartment;
                }
                if (!empty($branchFrAssignedAdmintrainingDepartment)) {
                    $branchesInfo['branchFrAssignedAdmintrainingDepartment'] =$branchFrAssignedAdmintrainingDepartment;
                }
                if (!empty($branchFrAssignedAdmissionDepartment)) {
                    $branchesInfo['branchFrAssignedAdmissionDepartment'] =$branchFrAssignedAdmissionDepartment;
                }
                if (!empty($branchFrAssignedMaterialDepartment)) {
                    $branchesInfo['branchFrAssignedMaterialDepartment'] =$branchFrAssignedMaterialDepartment;
                }
                if (!empty($branchFrAssignedDigitalDepartment)) {
                    $branchesInfo['branchFrAssignedDigitalDepartment'] =$branchFrAssignedDigitalDepartment;
                }
                if (!empty($branchFrAssignedTrainingDepartment)) {
                    $branchesInfo['branchFrAssignedTrainingDepartment'] =$branchFrAssignedTrainingDepartment;
                }
                if (!empty($branchFrAssignedSocialmediaDepartment)) {
                    $branchesInfo['branchFrAssignedSocialmediaDepartment'] =$branchFrAssignedSocialmediaDepartment;
                }
                  
                if (!empty($branchAddress)) {
                    $branchesInfo['branchAddress'] =$branchAddress;
                }
                if (!empty($permanentAddress)) {
                    $branchesInfo['permanentAddress'] =$permanentAddress;
                }
                if (!empty($franchiseNumber)) {
                    $branchesInfo['franchiseNumber'] =$franchiseNumber;
                }
                if (!empty($franchiseName)) {
                    $branchesInfo['franchiseName'] =$franchiseName;
                }
                 if (!empty($franchiseName)) {
                    $branchesInfo['trainingcat'] =$franchiseName;
                }
                if (!empty($typeBranch)) {
                    $branchesInfo['typeBranch'] =$typeBranch;
                }
                if (!empty($currentStatus)) {
                    $branchesInfo['currentStatus'] =$currentStatus;
                }
                if (!empty($bookingDate)) {
                    $branchesInfo['bookingDate'] =$bookingDate;
                }
                if (!empty($licenseNumber)) {
                    $branchesInfo['licenseNumber'] =$licenseNumber;
                }
                if (!empty($licenseSharedon)) {
                    $branchesInfo['licenseSharedon'] =$licenseSharedon;
                }
                if (!empty($validFromDate)) {
                    $branchesInfo['validFromDate'] =$validFromDate;
                }
                if (!empty($validTillDate)) {
                    $branchesInfo['validTillDate'] =$validTillDate;
                }
                if (!empty($branchLocation)) {
                    $branchesInfo['branchLocation'] =$branchLocation;
                }
                if (!empty($adminName)) {
                    $branchesInfo['adminName'] =$adminName;
                }
                if (!empty($adminContactNum)) {
                    $branchesInfo['adminContactNum'] =$adminContactNum;
                }
                if (!empty($additionalNumber)) {
                    $branchesInfo['additionalNumber'] =$additionalNumber;
                }
                if (!empty($officialEmailID)) {
                    $branchesInfo['officialEmailID'] =$officialEmailID;
                }
                if (!empty($personalEmailId)) {
                    $branchesInfo['personalEmailId'] =$personalEmailId;
                }
                if (!empty($biometricInstalled)) {
                    $branchesInfo['biometricInstalled'] =$biometricInstalled;
                }
                if (!empty($biometricRemark)) {
                    $branchesInfo['biometricRemark'] =$biometricRemark;
                }
                if (!empty($biometricInstalledDate)) {
                    $branchesInfo['biometricInstalledDate'] =$biometricInstalledDate;
                }
                if (!empty($camaraRemark)) {
                    $branchesInfo['camaraRemark'] =$camaraRemark;
                }
                if (!empty($camaraInstalled)) {
                    $branchesInfo['camaraInstalled'] =$camaraInstalled;
                }
                if (!empty($camaraInstalledDate)) {
                    $branchesInfo['camaraInstalledDate'] =$camaraInstalledDate;
                }
                if (!empty($eduMetaAppTraining)) {
                    $branchesInfo['eduMetaAppTraining'] =$eduMetaAppTraining;
                }
                if (!empty($AppTrainingRemark)) {
                    $branchesInfo['AppTrainingRemark'] =$AppTrainingRemark;
                }
                if (!empty($AppTrainingRemarkDate)) {
                    $branchesInfo['AppTrainingRemarkDate'] =$AppTrainingRemarkDate;
                }
                if (!empty($congratulationsImg)) {
                    $branchesInfo['congratulationsImg'] =$congratulationsImg;
                }
                if (!empty($brimguploadedFBStatus)) {
                    $branchesInfo['brimguploadedFBStatus'] =$brimguploadedFBStatus;
                }
                if (!empty($brimguploadedFBDate)) {
                    $branchesInfo['brimguploadedFBDate'] =$brimguploadedFBDate;
                }
                if (!empty($brimguploadedInstaStatus)) {
                    $branchesInfo['brimguploadedInstaStatus'] =$brimguploadedInstaStatus;
                }
                if (!empty($brimguploadedInstaDate)) {
                    $branchesInfo['brimguploadedInstaDate'] =$brimguploadedInstaDate;
                }
                if (!empty($admissionOpenimgStatus)) {
                    $branchesInfo['admissionOpenimgStatus'] =$admissionOpenimgStatus;
                }
                if (!empty($staffHiringimgStatus)) {
                    $branchesInfo['staffHiringimgStatus'] =$staffHiringimgStatus;
                }
                if (!empty($newsletterMarch)) {
                    $branchesInfo['newsletterMarch'] =$newsletterMarch;
                }
                if (!empty($newsletterJune)) {
                    $branchesInfo['newsletterJune'] =$newsletterJune;
                }
                if (!empty($newsletterSeptember)) {
                    $branchesInfo['newsletterSeptember'] =$newsletterSeptember;
                }
                if (!empty($newsletterDecember)) {
                    $branchesInfo['newsletterDecember'] =$newsletterDecember;
                }
                if (!empty($OBirthDayImgStatus)) {
                    $branchesInfo['OBirthDayImgStatus'] =$OBirthDayImgStatus;
                }
                if (!empty($OBirthDayImgSharedDtm)) {
                    $branchesInfo['OBirthDayImgSharedDtm'] =$OBirthDayImgSharedDtm;
                }
                if (!empty($OwnerAnnImgStatus)) {
                    $branchesInfo['OwnerAnnImgStatus'] =$OwnerAnnImgStatus;
                }
                if (!empty($OwnerAnnImgSharedDtm)) {
                    $branchesInfo['OwnerAnnImgSharedDtm'] =$OwnerAnnImgSharedDtm;
                }
                if (!empty($facebookPageStatus)) {
                    $branchesInfo['facebookPageStatus'] =$facebookPageStatus;
                }
                if (!empty($facebookPageLink)) {
                    $branchesInfo['facebookPageLink'] =$facebookPageLink;
                }
                if (!empty($facebookPageRemark)) {
                    $branchesInfo['facebookPageRemark'] =$facebookPageRemark;
                }
                if (!empty($googleMapLoc)) {
                    $branchesInfo['googleMapLoc'] =$googleMapLoc;
                }
                if(!empty($googleMapLoc)){
                    $branchesInfo['googleMapLoc']=$googleMapLoc;
                }
                if(!empty($googleMapLocLink)){
                    $branchesInfo['googleMapLocLink']=$googleMapLocLink;
                }
                if(!empty($googleMapLocRemark)){
                    $branchesInfo['googleMapLocRemark']=$googleMapLocRemark;
                }
                if(!empty($instagramPageStatus)){
                    $branchesInfo['instagramPageStatus']=$instagramPageStatus;
                }
                if(!empty($instagramPageID)){
                    $branchesInfo['instagramPageID']=$instagramPageID;
                }
                if(!empty($instagramPageRemark)){
                    $branchesInfo['instagramPageRemark']=$instagramPageRemark;
                }
                if(!empty($jdPageStatus)){
                    $branchesInfo['jdPageStatus']=$jdPageStatus;
                }
                if(!empty($jdPageID)){
                    $branchesInfo['jdPageID']=$jdPageID;
                }
                if(!empty($jdPageRemark)){
                    $branchesInfo['jdPageRemark']=$jdPageRemark;
                }
                if(!empty($tweetPageStatus)){
                    $branchesInfo['tweetPageStatus']=$tweetPageStatus;
                }
                if(!empty($tweetPageID)){
                    $branchesInfo['tweetPageID']=$tweetPageID;
                }
                if(!empty($tweetPageRemark)){
                    $branchesInfo['tweetPageRemark']=$tweetPageRemark;
                }
                if(!empty($digiMarkCost)){
                    $branchesInfo['digiMarkCost']=$digiMarkCost;
                }
                if(!empty($digiMarkStartDtm)){
                    $branchesInfo['digiMarkStartDtm']=$digiMarkStartDtm;
                }
                if(!empty($digiMarkEndDtm)){
                    $branchesInfo['digiMarkEndDtm']=$digiMarkEndDtm;
                }
                if(!empty($digiMarkReamrk)){
                    $branchesInfo['digiMarkReamrk']=$digiMarkReamrk;
                }
                if(!empty($insfeedvideoUplodFB)){
                    $branchesInfo['insfeedvideoUplodFB']=$insfeedvideoUplodFB;
                }
                if(!empty($insfeedvideoUplodYoutube)){
                    $branchesInfo['insfeedvideoUplodYoutube']=$insfeedvideoUplodYoutube;
                }
                if(!empty($insfeedvideoUplodInsta)){
                    $branchesInfo['insfeedvideoUplodInsta']=$insfeedvideoUplodInsta;
                }
                if(!empty($branchLocAddressPremise)){
                    $branchesInfo['branchLocAddressPremise']=$branchLocAddressPremise;
                }
                if(!empty($addOfFranchise)){
                    $branchesInfo['addOfFranchise']=$addOfFranchise;
                }
                if(!empty($gstNumber)){
                    $branchesInfo['gstNumber']=$gstNumber;
                }
                if(!empty($undertakingCommitmentSupport)){
                    $branchesInfo['undertakingCommitmentSupport']=$undertakingCommitmentSupport;
                }
                if(!empty($amcAmount)){
                    $branchesInfo['amcAmount']=$amcAmount;
                }
                if(!empty($invoiceNumber)){
                    $branchesInfo['invoiceNumber']=$invoiceNumber;
                }
                if(!empty($agreementTenure)){
                    $branchesInfo['agreementTenure']=$agreementTenure;
                }
                if(!empty($salesExecutive)){
                    $branchesInfo['salesExecutive']=$salesExecutive;
                }
                if(!empty($salesTeamlead)){
                    $branchesInfo['salesTeamlead']=$salesTeamlead;
                }
                if(!empty($Manual1)){
                    $branchesInfo['Manual1']=$Manual1;
                }
                if(!empty($Manual2)){
                    $branchesInfo['Manual2']=$Manual2;
                }
                if(!empty($Manual3)){
                    $branchesInfo['Manual3']=$Manual3;
                }
                if(!empty($Reference)){
                    $branchesInfo['Reference']=$Reference;
                }
                if(!empty($installationTentativeDate)){
                    $branchesInfo['installationTentativeDate']=$installationTentativeDate;
                }
                if(!empty($formsDocumentsCompleted)){
                    $branchesInfo['formsDocumentsCompleted']=$formsDocumentsCompleted;
                }
                if(!empty($setUpInstallation)){
                    $branchesInfo['setUpInstallation']=$setUpInstallation;
                }
                if(!empty($branchAnniversaryDate)){
                    $branchesInfo['branchAnniversaryDate']=$branchAnniversaryDate;
                }
                if(!empty($admissionCracked)){
                    $branchesInfo['admissionCracked']=$admissionCracked;
                }
                if(!empty($teacherRecruitment)){
                    $branchesInfo['teacherRecruitment']=$teacherRecruitment;
                }
                if(!empty($pgDecidedFee)){
                    $branchesInfo['pgDecidedFee']=$pgDecidedFee;
                }
                if(!empty($nurseryDecidedFee)){
                    $branchesInfo['nurseryDecidedFee']=$nurseryDecidedFee;
                }
                if(!empty($KG1DecidedFee)){
                    $branchesInfo['KG1DecidedFee']=$KG1DecidedFee;
                }
                if(!empty($KG2DecidedFee)){
                    $branchesInfo['KG2DecidedFee']=$KG2DecidedFee;
                }
                if(!empty($feeSharedStatus)){
                    $branchesInfo['feeSharedStatus']=$feeSharedStatus;
                }
                if(!empty($feesRemark)){
                    $branchesInfo['feesRemark']=$feesRemark;
                }
                if(!empty($addmissionPG)){
                    $branchesInfo['addmissionPG']=$addmissionPG;
                }
                if(!empty($addmissionNursary)){
                    $branchesInfo['addmissionNursary']=$addmissionNursary;
                }
                if(!empty($addmissionKg1)){
                    $branchesInfo['addmissionKg1']=$addmissionKg1;
                }
                if(!empty($addmissionKg2)){
                    $branchesInfo['addmissionKg2']=$addmissionKg2;
                }
                if(!empty($addmission1st)){
                    $branchesInfo['addmission1st']=$addmission1st;
                }
                if(!empty($addmission2nd)){
                    $branchesInfo['addmission2nd']=$addmission2nd;
                }
                if(!empty($totalAddmission)){
                    $branchesInfo['totalAddmission']=$totalAddmission;
                }
                if(!empty($addmissionCounselor)){
                    $branchesInfo['addmissionCounselor']=$addmissionCounselor;
                }
                if(!empty($lastDiscussaddmission)){
                    $branchesInfo['lastDiscussaddmission']=$lastDiscussaddmission;
                }
                if(!empty($addmissionSheetlink)){
                    $branchesInfo['addmissionSheetlink']=$addmissionSheetlink;
                }
                if(!empty($dateexlSheetshared)){
                    $branchesInfo['dateexlSheetshared']=$dateexlSheetshared;
                }
                if(!empty($lastInteractiondate)){
                    $branchesInfo['lastInteractiondate']=$lastInteractiondate;
                }
                if(!empty($lastDiscussionby)){
                    $branchesInfo['lastDiscussionby']=$lastDiscussionby;
                }
                if(!empty($lastInteractioncomment)){
                    $branchesInfo['lastInteractioncomment']=$lastInteractioncomment;
                }
                if(!empty($agreementDraftdate)){
                    $branchesInfo['agreementDraftdate']=$agreementDraftdate;
                }
                if(!empty($branchLandline)){
                    $branchesInfo['branchLandline' ]=$branchLandline;
                }
                if(!empty($additionalName)){
                    $branchesInfo['additionalName']=$additionalName;
                }
                if(!empty($finalPaydeadline)){
                    $branchesInfo['finalPaydeadline']=$finalPaydeadline;
                }
                if(!empty($BranchSpecialNoteSales)){
                    $branchesInfo['BranchSpecialNoteSales']=$BranchSpecialNoteSales;
                }
                if(!empty($completeFranchiseAmt)){
                    $branchesInfo['completeFranchiseAmt']=$completeFranchiseAmt;
                }
                if(!empty($confirmationAmt33kGST)){
                    $branchesInfo['confirmationAmt33kGST']=$confirmationAmt33kGST;
                }
                if(!empty($happinessLevelbranch)){
                    $branchesInfo['happinessLevelbranch']=$happinessLevelbranch;
                }
                if(!empty($DesignsPromotional)){
                    $branchesInfo['DesignsPromotional']=$DesignsPromotional;
                }
                if(!empty($DesignsPromotionalRemark)){
                    $branchesInfo['DesignsPromotionalRemark']=$DesignsPromotionalRemark;
                }
                if(!empty($BranchSpecialNote)){
                    $branchesInfo['BranchSpecialNote']=$BranchSpecialNote;
                }
                if(!empty($OwnerAnniversery)){
                    $branchesInfo['OwnerAnniversery']=$OwnerAnniversery;
                }
                if(!empty($welcomeCall)){
                    $branchesInfo['welcomeCall']=$welcomeCall;
                }
                if(!empty($welcomeMail)){
                    $branchesInfo['welcomeMail']=$welcomeMail;
                }
                if(!empty($whatsappGroup)){
                    $branchesInfo['whatsappGroup']=$whatsappGroup;
                }
                if(!empty($whatsappGroupRemark)){
                    $branchesInfo['whatsappGroupRemark']=$whatsappGroupRemark;
                }
                if(!empty($whatsappGroupdate)){
                    $branchesInfo['whatsappGroupdate']=$whatsappGroupdate;
                }
                if(!empty($interactionMeeting)){
                    $branchesInfo['interactionMeeting']=$interactionMeeting;
                }
                if(!empty($interactionMeetingRemark)){
                    $branchesInfo['interactionMeetingRemark']=$interactionMeetingRemark;
                }
                if(!empty($undertakingCommitment)){
                    $branchesInfo['undertakingCommitment']=$undertakingCommitment;
                }
                if(!empty($onboardingForm)){
                    $branchesInfo['onboardingForm']=$onboardingForm;
                }
                if(!empty($onboardingFormReceived)){
                    $branchesInfo['onboardingFormReceived']=$onboardingFormReceived;
                }
                if(!empty($onboardingFormRemark)){
                    $branchesInfo['onboardingFormRemark']=$onboardingFormRemark;
                }
                if(!empty($installationRequirementmail)){
                    $branchesInfo['installationRequirementmail']=$installationRequirementmail;
                }
                if(!empty($installationRequirementmailRemark)){
                    $branchesInfo['installationRequirementmailRemark']=$installationRequirementmailRemark;
                }
                if(!empty($finalAgreementShared)){
                    $branchesInfo['finalAgreementShared']=$finalAgreementShared;
                }
                if(!empty($agreementDraftReceiveddate)){    
                    $branchesInfo['agreementDraftReceiveddate']=$agreementDraftReceiveddate;
                }
                if(!empty($compFileSubmit)){    
                    $branchesInfo['compFileSubmit']=$compFileSubmit;
                }
                if(!empty($fileCLoserDate)){    
                    $branchesInfo['fileCLoserDate']=$fileCLoserDate;
                }
                if(!empty($branchStatusRemark)){    
                    $branchesInfo['branchStatusRemark']=$branchStatusRemark;
                }
                if(!empty($officialemailshared)){
                    $branchesInfo['officialemailshared']=$officialemailshared;
                }
               
                if(!empty($inaugurationDate)){
                    $branchesInfo['inaugurationDate']=$inaugurationDate;
                }
                if(!empty($classroomDecoration)){
                    $branchesInfo['classroomDecoration']=$classroomDecoration;
                }
                if(!empty($movieClub)){
                    $branchesInfo['movieClub']=$movieClub;
                }
                if(!empty($referEarn)){
                    $branchesInfo['referEarn']=$referEarn;
                }
                if(!empty($teacherInteraction)){
                    $branchesInfo['teacherInteraction']=$teacherInteraction;
                }
                if(!empty($teacherInterview)){
                    $branchesInfo['teacherInterview']=$teacherInterview;
                }
                if(!empty($pongalWorkshop)){
                    $branchesInfo['pongalWorkshop']=$pongalWorkshop;
                }
                if(!empty($sankrantiWorkshop)){
                    $branchesInfo['sankrantiWorkshop']=$sankrantiWorkshop;
                }
                if(!empty($republicDayWorkshop)){
                    $branchesInfo['republicDayWorkshop']=$republicDayWorkshop;
                }
                if(!empty($bridgeCourseCounselling)){
                    $branchesInfo['bridgeCourseCounselling']=$bridgeCourseCounselling;
                }
                if(!empty($bulletinBoard)){
                    $branchesInfo['bulletinBoard']=$bulletinBoard;
                }
                if(!empty($bridgeCourse)){
                    $branchesInfo['bridgeCourse']=$bridgeCourse;
                }
                if(!empty($settlersProgram)){
                    $branchesInfo['settlersProgram']=$settlersProgram;
                }
                if(!empty($jollyPhonic)){
                    $branchesInfo['jollyPhonic']=$jollyPhonic;
                }
                if(!empty($academicsMeetings)){
                    $branchesInfo['academicsMeetings']=$academicsMeetings;
                }
                if(!empty($timeDisclipineemail)){
                    $branchesInfo['timeDisclipineemail']=$timeDisclipineemail;
                }
                if(!empty($uniformDisclipineemail)){
                    $branchesInfo['uniformDisclipineemail']=$uniformDisclipineemail;
                }
                if(!empty($curiculumnShared)){
                    $branchesInfo['curiculumnShared']=$curiculumnShared;
                }
                if(!empty($holidaEventlisting)){
                    $branchesInfo['holidaEventlisting']=$holidaEventlisting;
                }
                if(!empty($sharingAssessmentpapers)){
                    $branchesInfo['sharingAssessmentpapers']=$sharingAssessmentpapers;
                }
                if(!empty($assessmentSharingemail)){
                    $branchesInfo['assessmentSharingemail']=$assessmentSharingemail;
                }
                if(!empty($PTMscheduledate)){
                    $branchesInfo['PTMscheduledate']=$PTMscheduledate;
                }
                if(!empty($shadowPuppet)){
                    $branchesInfo['shadowPuppet']=$shadowPuppet;
                }
                if(!empty($monthlyEventtraining)){
                    $branchesInfo['monthlyEventtraining']=$monthlyEventtraining;
                }
                if(!empty($summertCampdate)){
                    $branchesInfo['summertCampdate']=$summertCampdate;
                }
                if(!empty($winterCampdate)){
                    $branchesInfo['winterCampdate']=$winterCampdate;
                }
                if(!empty($offerName)){
                    $branchesInfo['offerName']=$offerName;
                }
                if(!empty($offerPlanname)){
                    $branchesInfo['offerPlanname']=$offerPlanname;
                }
                if(!empty($discountAmount)){
                    $branchesInfo['discountAmount']=$discountAmount;
                }
                if(!empty($finalAmount)){
                    $branchesInfo['finalAmount']=$finalAmount;
                }
                if(!empty($welComeFolderStatus)){
                    $branchesInfo['welComeFolderStatus']=$welComeFolderStatus;
                }
                if(!empty($welComeFolderDtm)){
                    $branchesInfo['welComeFolderDtm']=$welComeFolderDtm;
                }
                if(!empty($trainingAmount)){
                    $branchesInfo['trainingAmount']=$trainingAmount;
                }
                if(!empty($societyServiceamount)){
                    $branchesInfo['societyServiceamount']=$societyServiceamount;
                }
                if(!empty($totalAmount)){
                    $branchesInfo['totalAmount']=$totalAmount;
                }
                if(!empty($gstAmount)){
                    $branchesInfo['gstAmount']=$gstAmount;
                }
                if(!empty($totalfranchisegstFund)){
                    $branchesInfo['totalfranchisegstFund']=$totalfranchisegstFund;
                }
                if(!empty($legalCharges)){
                    $branchesInfo['legalCharges']=$legalCharges;
                }
                if(!empty($legalChargesdue)){
                    $branchesInfo['legalChargesdue']=$legalChargesdue;
                }
                if(!empty($totalgstCharges)){
                    $branchesInfo['totalgstCharges']=$totalgstCharges;
                }
                if(!empty($totalPaidamount)){
                    $branchesInfo['totalPaidamount']=$totalPaidamount;
                }
                if(!empty($dueFranchiseamt)){
                    $branchesInfo['dueFranchiseamt']=$dueFranchiseamt;
                }
                if(!empty($kitCharges)){
                    $branchesInfo['kitCharges']=$kitCharges;
                }
                if(!empty($numinitialKit)){
                    $branchesInfo['numinitialKit']=$numinitialKit;
                }
                if(!empty($totalKitsamt)){
                    $branchesInfo['totalKitsamt']=$totalKitsamt;
                }
                if(!empty($kitamtReceived)){
                    $branchesInfo['kitamtReceived']=$kitamtReceived;
                }
                if(!empty($dueKitamount)){
                    $branchesInfo['dueKitamount']=$dueKitamount;
                }
                if(!empty($installationDate)){
                    $branchesInfo['installationDate']=$installationDate;
                }
                if(!empty($finaltotalamtDue)){
                    $branchesInfo['finaltotalamtDue']=$finaltotalamtDue;
                }
                if(!empty($specialRemark)){
                    $branchesInfo['specialRemark']=$specialRemark;
                }
                if(!empty($transporttravCharge)){
                    $branchesInfo['transporttravCharge']=$transporttravCharge;
                }
                if(!empty($brsetupinstachargReceived)){
                    $branchesInfo['brsetupinstachargReceived']=$brsetupinstachargReceived;
                }
                if(!empty($brsetupinstachargDue)){
                    $branchesInfo['brsetupinstachargDue']=$brsetupinstachargDue;
                }
                if(!empty($travelAmount)){
                    $branchesInfo['travelAmount']=$travelAmount;
                }
                if(!empty($receivedtravelAmount)){
                    $branchesInfo['receivedtravelAmount']=$receivedtravelAmount;
                }
                if(!empty($duetravelAmount)){
                    $branchesInfo['duetravelAmount']=$duetravelAmount;
                }
                if(!empty($transportCharges)){
                    $branchesInfo['transportCharges']=$transportCharges;
                }
                if(!empty($transportAmtreceived)){
                    $branchesInfo['transportAmtreceived']=$transportAmtreceived;
                }
                if(!empty($duetransportCharges)){
                    $branchesInfo['duetransportCharges']=$duetransportCharges;
                }
                if(!empty($ledgerMarch)){
                    $branchesInfo['ledgerMarch']=$ledgerMarch;
                }
                if(!empty($ledgerJune)){
                    $branchesInfo['ledgerJune']=$ledgerJune;
                }
                if(!empty($ledgerSeptember)){
                    $branchesInfo['ledgerSeptember']=$ledgerSeptember;
                }
                if(!empty($ledgerDecember)){
                    $branchesInfo['ledgerDecember']=$ledgerDecember;
                }
                if(!empty($reminderAMCStatus1Dec)){
                    $branchesInfo['reminderAMCStatus1Dec']=$reminderAMCStatus1Dec;
                }
                if(!empty($reminderAMCStatus10Dec)){
                    $branchesInfo['reminderAMCStatus10Dec']=$reminderAMCStatus10Dec;
                }
                if(!empty($reminderAMCStatus15Dec)){
                    $branchesInfo['reminderAMCStatus15Dec']=$reminderAMCStatus15Dec;
                }
                if(!empty($reminderAMCStatus19Dec)){
                    $branchesInfo['reminderAMCStatus19Dec']=$reminderAMCStatus19Dec;
                }
                if(!empty($reminderAMCStatus20Dec)){
                    $branchesInfo['reminderAMCStatus20Dec']=$reminderAMCStatus20Dec;
                }
                if(!empty($RemarkforAMCmail)){
                    $branchesInfo['RemarkforAMCmail']=$RemarkforAMCmail;
                }
                if(!empty($InvoiceAMCClearance)){
                    $branchesInfo['InvoiceAMCClearance']=$InvoiceAMCClearance;
                }
                if(!empty($PenaltyMailnoncle)){
                    $branchesInfo['PenaltyMailnoncle']=$PenaltyMailnoncle;
                }
                if(!empty($invoiceNumberAll)){
                    $branchesInfo['invoiceNumberAll']=$invoiceNumberAll;
                }
                if(!empty($upgradeUptoclass)){
                    $branchesInfo['upgradeUptoclass']=$upgradeUptoclass;
                }
                if(!empty($branchStatus)){
                    $branchesInfo['branchStatus']=$branchStatus;
                }
                if(!empty($brInstallationStatus)){
                    $branchesInfo['brInstallationStatus']=$brInstallationStatus;
                }
                if(!empty($undertakingAck)){
                    $branchesInfo['undertakingAck']=$undertakingAck;
                }
                if(!empty($optOnlineMarketing)){
                    $branchesInfo['optOnlineMarketing']=$optOnlineMarketing;
                }
                if(!empty($insmatDispatchdate)){
                    $branchesInfo['insmatDispatchdate']=$insmatDispatchdate;
                }
                if(!empty($DetailsReceiptmail)){
                    $branchesInfo['DetailsReceiptmail']=$DetailsReceiptmail;
                }
                if(!empty($ConfBrinsScheduledemail)){
                    $branchesInfo['ConfBrinsScheduledemail']=$ConfBrinsScheduledemail;
                }
                if(!empty($Materialrecdate)){
                    $branchesInfo['Materialrecdate']=$Materialrecdate;
                }
                if(!empty($BrinsScheduleddate)){
                    $branchesInfo['BrinsScheduleddate']=$BrinsScheduleddate;
                }
                if(!empty($BrinsScheduledemail)){
                    $branchesInfo['BrinsScheduledemail']=$BrinsScheduledemail;
                }
                if(!empty($brInstalationRemark)){
                    $branchesInfo['brInstalationRemark']=$brInstalationRemark;
                }
                if(!empty($videoFeedbackbr)){
                    $branchesInfo['videoFeedbackbr']=$videoFeedbackbr;
                }
                if(!empty($writtenFeedbackbr)){
                    $branchesInfo['writtenFeedbackbr']=$writtenFeedbackbr;
                }
                if(!empty($ShoppinPortSharedDate)){
                    $branchesInfo['ShoppinPortSharedDate']=$ShoppinPortSharedDate;
                }
                if(!empty($ShoppinPortTraining)){
                    $branchesInfo['ShoppinPortTraining']=$ShoppinPortTraining;
                }
                if(!empty($ShoppinPortTrainingDate)){
                    $branchesInfo['ShoppinPortTrainingDate']=$ShoppinPortTrainingDate;
                }
                if(!empty($ShoppinPortRemark)){
                    $branchesInfo['ShoppinPortRemark']=$ShoppinPortRemark;
                }
                if(!empty($returnItems)){
                    $branchesInfo['returnItems']=$returnItems;
                }
                if(!empty($modeOfDespatch)){
                    $branchesInfo['modeOfDespatch']=$modeOfDespatch;
                }
                if(!empty($NumOfBoxes)){
                    $branchesInfo['NumOfBoxes']=$NumOfBoxes;
                }
                if(!empty($PoDNum)){
                    $branchesInfo['PoDNum']=$PoDNum;
                }
                if(!empty($SpecificGiftOffer)){
                    $branchesInfo['SpecificGiftOffer']=$SpecificGiftOffer;
                }
                if(!empty($ConfBrInsOverPhone)){
                    $branchesInfo['ConfBrInsOverPhone']=$ConfBrInsOverPhone;
                }
                if(!empty($shortComming)){
                    $branchesInfo['shortComming']=$shortComming;
                }
                if(!empty($solutionShortComming)){
                    $branchesInfo['solutionShortComming']=$solutionShortComming;
                }
                if(!empty($customWebsiteLink)){
                    $branchesInfo['customWebsiteLink']=$customWebsiteLink;
                }
                 if(!empty($ledgerMonth)){
                    $branchesInfo['ledgerMonth']=$ledgerMonth;
                }
                if(!empty($LedgerYear)){
                    $branchesInfo['LedgerYear']=$LedgerYear;
                }
                 
                 if (!empty($IntroductionDate)) {
    $branchesInfo['IntroductionDate'] = $IntroductionDate;
}
if (!empty($Pre_marketingDate)) {
    $branchesInfo['Pre_marketingDate'] = $Pre_marketingDate;
}
if (!empty($Admin_OrientationDate)) {
    $branchesInfo['Admin_OrientationDate'] = $Admin_OrientationDate;
}
if (!empty($Inauguration_Refer_and_EarnDate)) {
    $branchesInfo['Inauguration_Refer_and_EarnDate'] = $Inauguration_Refer_and_EarnDate;
}
if (!empty($Classroom_decorationDate)) {
    $branchesInfo['Classroom_decorationDate'] = $Classroom_decorationDate;
}
if (!empty($Movie_clubDate)) {
    $branchesInfo['Movie_clubDate'] = $Movie_clubDate;
}
if (!empty($Fee_structureDate)) {
    $branchesInfo['Fee_structureDate'] = $Fee_structureDate;
}
if (!empty($Day_careDate)) {
    $branchesInfo['Day_careDate'] = $Day_careDate;
}
if (!empty($ToddlerDate)) {
    $branchesInfo['ToddlerDate'] = $ToddlerDate;
}
if (!empty($pG_April_JuneDate)) {
    $branchesInfo['pG_April_JuneDate'] = $pG_April_JuneDate;
}
if (!empty($pG_JulyDate)) {
    $branchesInfo['pG_JulyDate'] = $pG_JulyDate;
}
if (!empty($pG_AugustDate)) {
    $branchesInfo['pG_AugustDate'] = $pG_AugustDate;
}
if (!empty($pG_SeptemberDate)) {
    $branchesInfo['pG_SeptemberDate'] = $pG_SeptemberDate;
}
if (!empty($pG_OctoberDate)) {
    $branchesInfo['pG_OctoberDate'] = $pG_OctoberDate;
}
if (!empty($pG_NovemberDate)) {
    $branchesInfo['pG_NovemberDate'] = $pG_NovemberDate;
}
if (!empty($pG_DecemberDate)) {
    $branchesInfo['pG_DecemberDate'] = $pG_DecemberDate;
}
if (!empty($pG_JanuaryDate)) {
    $branchesInfo['pG_JanuaryDate'] = $pG_JanuaryDate;
}
if (!empty($pG_FebruaryDate)) {
    $branchesInfo['pG_FebruaryDate'] = $pG_FebruaryDate;
}
if (!empty($pG_MarchDate)) {
    $branchesInfo['pG_MarchDate'] = $pG_MarchDate;
}
if (!empty($NurseryBook_1_Date)) {
    $branchesInfo['NurseryBook_1_Date'] = $NurseryBook_1_Date;
}
if (!empty($NurseryBook_2_Date)) {
    $branchesInfo['NurseryBook_2_Date'] = $NurseryBook_2_Date;
}
if (!empty($NurseryBook_3_Date)) {
    $branchesInfo['NurseryBook_3_Date'] = $NurseryBook_3_Date;
}
if (!empty($NurseryBook_4_Date)) {
    $branchesInfo['NurseryBook_4_Date'] = $NurseryBook_4_Date;
}
if (!empty($NurseryBook_5_Date)) {
    $branchesInfo['NurseryBook_5_Date'] = $NurseryBook_5_Date;
}
if (!empty($NurseryBook_6_Date)) {
    $branchesInfo['NurseryBook_6_Date'] = $NurseryBook_6_Date;
}
if (!empty($NurseryBook_7_Date)) {
    $branchesInfo['NurseryBook_7_Date'] = $NurseryBook_7_Date;
}
if (!empty($NurseryBook_8_Date)) {
    $branchesInfo['NurseryBook_8_Date'] = $NurseryBook_8_Date;
}
if (!empty($NurseryBook_9_Date)) {
    $branchesInfo['NurseryBook_9_Date'] = $NurseryBook_9_Date;
}
if (!empty($KG1Book_1_Date)) {
    $branchesInfo['KG1Book_1_Date'] = $KG1Book_1_Date;
}
if (!empty($KG1Book_2_Date)) {
    $branchesInfo['KG1Book_2_Date'] = $KG1Book_2_Date;
}
if (!empty($KG1Book_3_Date)) {
    $branchesInfo['KG1Book_3_Date'] = $KG1Book_3_Date;
}
if (!empty($KG1Book_4_Date)) {
    $branchesInfo['KG1Book_4_Date'] = $KG1Book_4_Date;
}
if (!empty($KG1Book_5_Date)) {
    $branchesInfo['KG1Book_5_Date'] = $KG1Book_5_Date;
}
if (!empty($KG1Book_6_Date)) {
    $branchesInfo['KG1Book_6_Date'] = $KG1Book_6_Date;
}
if (!empty($KG1Book_7_Date)) {
    $branchesInfo['KG1Book_7_Date'] = $KG1Book_7_Date;
}
if (!empty($KG1Book_8_Date)) {
    $branchesInfo['KG1Book_8_Date'] = $KG1Book_8_Date;
}
if (!empty($KG1Book_9_Date)) {
    $branchesInfo['KG1Book_9_Date'] = $KG1Book_9_Date;
}
if (!empty($KG2Book_1_Date)) {
    $branchesInfo['KG2Book_1_Date'] = $KG2Book_1_Date;
}
if (!empty($KG2Book_2_Date)) {
    $branchesInfo['KG2Book_2_Date'] = $KG2Book_2_Date;
}
if (!empty($KG2Book_3_Date)) {
    $branchesInfo['KG2Book_3_Date'] = $KG2Book_3_Date;
}
if (!empty($KG2Book_4_Date)) {
    $branchesInfo['KG2Book_4_Date'] = $KG2Book_4_Date;
}
if (!empty($KG2Book_5_Date)) {
    $branchesInfo['KG2Book_5_Date'] = $KG2Book_5_Date;
}
if (!empty($KG2Book_6_Date)) {
    $branchesInfo['KG2Book_6_Date'] = $KG2Book_6_Date;
}
if (!empty($KG2Book_7_Date)) {
    $branchesInfo['KG2Book_7_Date'] = $KG2Book_7_Date;
}
if (!empty($KG2Book_8_Date)) {
    $branchesInfo['KG2Book_8_Date'] = $KG2Book_8_Date;
}
if (!empty($KG2Book_9_Date)) {
    $branchesInfo['KG2Book_9_Date'] = $KG2Book_9_Date;
}
if (!empty($eventCelebration_April_JuneDate)) {
    $branchesInfo['eventCelebration_April_JuneDate'] = $eventCelebration_April_JuneDate;
}
if (!empty($eventCelebration_JulyDate)) {
    $branchesInfo['eventCelebration_JulyDate'] = $eventCelebration_JulyDate;
}
if (!empty($eventCelebration_AugustDate)) {
    $branchesInfo['eventCelebration_AugustDate'] = $eventCelebration_AugustDate;
}
if (!empty($eventCelebration_SeptemberDate)) {
    $branchesInfo['eventCelebration_SeptemberDate'] = $eventCelebration_SeptemberDate;
}
if (!empty($eventCelebration_OctoberDate)) {
    $branchesInfo['eventCelebration_OctoberDate'] = $eventCelebration_OctoberDate;
}
if (!empty($eventCelebration_NovemberDate)) {
    $branchesInfo['eventCelebration_NovemberDate'] = $eventCelebration_NovemberDate;
}
if (!empty($eventCelebration_DecemberDate)) {
    $branchesInfo['eventCelebration_DecemberDate'] = $eventCelebration_DecemberDate;
}
if (!empty($eventCelebration_JanuaryDate)) {
    $branchesInfo['eventCelebration_JanuaryDate'] = $eventCelebration_JanuaryDate;
}
if (!empty($eventCelebration_FebruaryDate)) {
    $branchesInfo['eventCelebration_FebruaryDate'] = $eventCelebration_FebruaryDate;
}
if (!empty($eventCelebration_MarchDate)) {
    $branchesInfo['eventCelebration_MarchDate'] = $eventCelebration_MarchDate;
}

// Workshop fields
if (!empty($Workshop_1Date)) {
    $branchesInfo['Workshop_1Date'] = $Workshop_1Date;
}
if (!empty($Workshop_2Date)) {
    $branchesInfo['Workshop_2Date'] = $Workshop_2Date;
}
if (!empty($Workshop_3Date)) {
    $branchesInfo['Workshop_3Date'] = $Workshop_3Date;
}
if (!empty($Workshop_4Date)) {
    $branchesInfo['Workshop_4Date'] = $Workshop_4Date;
}
if (!empty($Workshop_5Date)) {
    $branchesInfo['Workshop_5Date'] = $Workshop_5Date;
}
if (!empty($Workshop_6Date)) {
    $branchesInfo['Workshop_6Date'] = $Workshop_6Date;
}
if (!empty($Workshop_7Date)) {
    $branchesInfo['Workshop_7Date'] = $Workshop_7Date;
}

// Others fields
if (!empty($others_Settlers_program_Date)) {
    $branchesInfo['others_Settlers_program_Date'] = $others_Settlers_program_Date;
}
if (!empty($others_Circle_time_Date)) {
    $branchesInfo['others_Circle_time_Date'] = $others_Circle_time_Date;
}
if (!empty($others_Interview_Date)) {
    $branchesInfo['others_Interview_Date'] = $others_Interview_Date;
}
if (!empty($others_Academic_Overview_Date)) {
    $branchesInfo['others_Academic_Overview_Date'] = $others_Academic_Overview_Date;
}
if (!empty($others_Teacher_interaction_Date)) {
    $branchesInfo['others_Teacher_interaction_Date'] = $others_Teacher_interaction_Date;
}
if (!empty($others_Assessment_Date)) {
    $branchesInfo['others_Assessment_Date'] = $others_Assessment_Date;
}
if (!empty($others_PTM_Date)) {
    $branchesInfo['others_PTM_Date'] = $others_PTM_Date;
}
if (!empty($others_Summer_camp_Date)) {
    $branchesInfo['others_Summer_camp_Date'] = $others_Summer_camp_Date;
}
if (!empty($others_Winter_Camp_Date)) {
    $branchesInfo['others_Winter_Camp_Date'] = $others_Winter_Camp_Date;
}
if (!empty($others_Aayam_Date)) {
    $branchesInfo['others_Aayam_Date'] = $others_Aayam_Date;
}
if (!empty($others_Sports_day_Date)) {
    $branchesInfo['others_Sports_day_Date'] = $others_Sports_day_Date;
}
if (!empty($others_midterm_session)) {
    $branchesInfo['others_midterm_session'] = $others_midterm_session;
}
if (!empty($others_bridgecourse)) {
    $branchesInfo['others_bridgecourse'] = $others_bridgecourse;
}

                $branchesInfo['createdBy']=$this->vendorId;
                $branchesInfo['createdDtm'] =date('Y-m-d H:i:s');
                
                
                $result = $this->bm->editBranches($branchesInfo, $branchesId);
      //  print_r($branchesInfo);exit;
   if ($result == true) {
                $this->load->model('Notification_model');
                $assignedUser = $this->Notification_model->get_assigned_user_by_branch($branchesId);

                if (!empty($assignedUser) && $assignedUser->branchFranchiseAssigned) {
                    $notificationMessage = "<strong>Branches:</strong> A new branch (" . $branchcityName . ") has been assigned to you.";
                    $this->Notification_model->add_branch_notification($assignedUser->branchFranchiseAssigned, $notificationMessage, $branchesId);

                    log_message('error', "✅ DEBUG: Notification Sent to Assigned User - UserID: " . $assignedUser->branchFranchiseAssigned);
                } else {
                    log_message('error', "❌ ERROR: No assigned user found for Branch ID - $branchesId");
                }

                $this->session->set_flashdata('success', 'Branches updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Branches updation failed');
            }

            redirect('branches/branchesListing');
        }
    }
}
    /**
     * This function is used to legal documents
     */
    private function addLegalDocuments($branchID)
    {
        $this->load->config('modules');

        $modules = $this->config->item('legalDocuments');

        $accessMatrix = array('branchesId'=>$branchID, 'access'=>json_encode($modules), 'createdBy'=> $this->vendorId, 'createdDtm'=>date('Y-m-d H:i:s'));

        $this->bm->insertLegalDocuments($accessMatrix);
    }


    /*** This function is used to import csv file*/
    public function uploadCsvFile() {
        // Load necessary libraries
        // $config['upload_path'] = FCPATH . 'uploads/';
        $config['upload_path']   = 'uploads/';
        $config['allowed_types'] = 'csv';

        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('csv_file')) {
            $error = array('error' => $this->upload->display_errors());
            $this->session->set_flashdata('error', 'Csv Upload failed');
            redirect('branches/branchesListing');
        } else {
            $data = array('upload_data' => $this->upload->data());
            $csv_file_path = $data['upload_data']['full_path'];

            // Process the CSV file and insert data into database
            $csv_data = array_map('str_getcsv', file($csv_file_path));
            // Start loop from the second row (index 1)
            for ($i = 1; $i < count($csv_data); $i++) {
                $row = $csv_data[$i];
                $nonEmptyFieldFound = false;
                foreach ($row as $field) {
                    if (!empty($field)) {
                        $nonEmptyFieldFound = true;
                        break;
                    }
                }

                if ($nonEmptyFieldFound) {

                    $data = array(
                        'applicantName'     => $row[0],
                        'address'           => $row[1],
                        'isDeleted'         => $row[2],
                        'createdBy'         => $row[3],
                        'createdDtm'        => $row[4],
                        'updatedBy'         => $row[5],
                        'updatedDtm'        => $row[6],
                        'mobile'            => $row[7],
                        'branchcityName'     => $row[8],
                        'branchState'       => $row[9],
                        'branchSalesDoneby' => $row[10],
                        'branchAmountReceived' => $row[11],
                        'branchFranchiseAssigned' => $row[12],
                        'branchFranchiseAssignedLegalDepartment' => $row[13],
                        'branchFrAssignedAccountsDepartment' => $row[14],
                        'branchFrAssignedDispatchDepartment' => $row[15],
                        'branchFrAssignedAdmissionDepartment' => $row[16],
                        'branchFrAssignedMaterialDepartment' => $row[17],
                        'branchFrAssignedDigitalDepartment' => $row[18],
                        'branchFrAssignedTrainingDepartment' => $row[19],
                        'BranchSpecialNoteSales' => $row[20],
                        'branchFranchiseAssignedDesigning' => $row[21],
                        'branchAddress' => $row[22],
                        'branchEmail' => $row[23],
                        'permanentAddress' => $row[24],
                        'franchiseNumber' => $row[25],
                        'franchiseName' => $row[26],
                        'typeBranch' => $row[27],
                        'currentStatus' => $row[28],
                        'bookingDate' => $row[29],
                        'licenseNumber' => $row[30],
                        'licenseSharedon' => $row[31],
                        'validFromDate' => $row[32],
                        'validTillDate' => $row[33],
                        'branchLocation' => $row[34],
                        'adminName' => $row[35],
                        'adminContactNum' => $row[36],
                        'additionalNumber' => $row[37],
                        'officialEmailID' => $row[38],
                        'personalEmailId' => $row[39],
                        'biometricInstalled' => $row[40],
                        'biometricRemark' => $row[41],
                        'biometricInstalledDate' => $row[42],
                        'camaraInstalled' => $row[43],
                        'camaraRemark' => $row[44],
                        'camaraInstalledDate' => $row[45],
                        'eduMetaAppTraining' => $row[46],
                        'AppTrainingRemark' => $row[47],
                        'AppTrainingRemarkDate' => $row[48],
                        'congratulationsImg' => $row[49],
                        'brimguploadedFBStatus' => $row[50],
                        'brimguploadedFBDate' => $row[51],
                        'brimguploadedInstaStatus' => $row[52],
                        'brimguploadedInstaDate' => $row[53],
                        'admissionOpenimgStatus' => $row[54],
                        'staffHiringimgStatus' => $row[55],
                        'newsletterMarch' => $row[56],
                        'newsletterJune' => $row[57],
                        'newsletterSeptember' => $row[58],
                        'newsletterDecember' => $row[59],
                        'OBirthDayImgStatus' => $row[60],
                        'OBirthDayImgSharedDtm' => $row[61],
                        'OwnerAnnImgStatus' => $row[62],
                        'OwnerAnnImgSharedDtm' => $row[63],
                        'facebookPageStatus' => $row[64],
                        'facebookPageLink' => $row[65],
                        'facebookPageRemark' => $row[66],
                        'googleMapLoc' => $row[67],
                        'googleMapLocLink' => $row[68],
                        'googleMapLocRemark' => $row[69],
                        'instagramPageStatus' => $row[70],
                        'instagramPageID' => $row[71],
                        'instagramPageRemark' => $row[72],
                        'jdPageStatus' => $row[73],
                        'jdPageID' => $row[74],
                        'jdPageRemark' => $row[75],
                        'tweetPageStatus' => $row[76],
                        'tweetPageID' => $row[77],
                        'tweetPageRemark' => $row[78],
                        'digiMarkCost' => $row[79],
                        'digiMarkStartDtm' => $row[80],
                        'digiMarkEndDtm' => $row[81],
                        'digiMarkReamrk' => $row[82],
                        'insfeedvideoUplodFB' => $row[83],
                        'insfeedvideoUplodYoutube' => $row[84],
                        'insfeedvideoUplodInsta' => $row[85],
                        'branchLocAddressPremise' => $row[86],
                        'addOfFranchise' => $row[87],
                        'gstNumber' => $row[88],
                        'amcAmount' => $row[89],
                        'invoiceNumber' => $row[90],
                        'agreementTenure' => $row[91],
                        'salesExecutive' => $row[92],
                        'salesTeamlead' => $row[93],
                        'Manual1' => $row[94],
                        'Manual2' => $row[95],
                        'Manual3' => $row[96],
                        'Reference' => $row[97],
                        'installationTentativeDate' => $row[98],
                        'formsDocumentsCompleted' => $row[99],
                        'setUpInstallation' => $row[100],
                        'branchAnniversaryDate' => $row[101],
                        'admissionCracked' => $row[102],
                        'teacherRecruitment' => $row[103],
                        'pgDecidedFee' => $row[104],
                        'nurseryDecidedFee' => $row[105],
                        'KG1DecidedFee' => $row[106],
                        'KG2DecidedFee' => $row[107],
                        'feeSharedStatus' => $row[108],
                        'feesRemark' => $row[109],
                        'totalAddmission' => $row[110],
                        'addmission2nd' => $row[111],
                        'addmission1st' => $row[112],
                        'addmissionKg2' => $row[113],
                        'addmissionKg1' => $row[114],
                        'addmissionNursary' => $row[115],
                        'addmissionPG' => $row[116],
                        'addmissionCounselor' => $row[117],
                        'lastDiscussaddmission' => $row[118],
                        'addmissionSheetlink' => $row[119],
                        'dateexlSheetshared' => $row[120],
                        'lastDiscussionby' => $row[121],
                        'lastInteractioncomment' => $row[122],
                        'branchLandline' => $row[123],
                        'additionalName' => $row[124],
                        'completeFranchiseAmt' => $row[125],
                        'happinessLevelbranch' => $row[126],
                        'agreementDraftdate' => $row[127],
                        'finalPaydeadline' => $row[128],
                        'confirmationAmt33kGST' => $row[129],
                        'lastInteractiondate' => $row[130],
                        'DesignsPromotional' => $row[131],
                        'DesignsPromotionalRemark' => $row[132],
                        'BranchSpecialNote' => $row[133],
                        'OwnerAnniversery' => $row[134],
                        'welcomeCall' => $row[135],
                        'welcomeMail' => $row[136],
                        'whatsappGroup' => $row[137],
                        'whatsappGroupRemark' => $row[138],
                        'whatsappGroupdate' => $row[139],
                        'interactionMeeting' => $row[140],
                        'interactionMeetingRemark' => $row[141],
                        'undertakingCommitment' => $row[142],
                        'onboardingForm' => $row[143],
                        'onboardingFormRemark' => $row[144],
                        'installationRequirementmail' => $row[145],
                        'installationRequirementmailRemark' => $row[146],
                        'officialemailshared' => $row[147],
                        'finalAgreementShared' => $row[148],
                        'agreementDraftReceiveddate' => $row[149],
                        'compFileSubmit' => $row[150],
                        'fileCLoserDate' => $row[151],
                        'branchStatusRemark' => $row[152],
                        'adminTraining' => $row[153],
                        'inaugurationDate' => $row[154],
                        'classroomDecoration' => $row[155],
                        'movieClub' => $row[156],
                        'referEarn' => $row[157],
                        'teacherInteraction' => $row[158],
                        'teacherInterview' => $row[159],
                        'pongalWorkshop' => $row[160],
                        'sankrantiWorkshop' => $row[161],
                        'republicDayWorkshop' => $row[162],
                        'bridgeCourseCounselling' => $row[163],
                        'bulletinBoard' => $row[164],
                        'bridgeCourse' => $row[165],
                        'settlersProgram' => $row[166],
                        'jollyPhonic' => $row[167],
                        'academicsMeetings' => $row[168],
                        'timeDisclipineemail' => $row[169],
                        'uniformDisclipineemail' => $row[170],
                        'curiculumnShared' => $row[171],
                        'holidaEventlisting' => $row[172],
                        'sharingAssessmentpapers' => $row[173],
                        'assessmentSharingemail' => $row[174],
                        'PTMscheduledate' => $row[175],
                        'shadowPuppet' => $row[176],
                        'monthlyEventtraining' => $row[177],
                        'summertCampdate' => $row[178],
                        'winterCampdate' => $row[179],
                        'offerName' => $row[180],
                        'offerPlanname' => $row[181],
                        'discountAmount' => $row[182],
                        'finalAmount' => $row[183],
                        'welComeFolderStatus' => $row[184],
                        'welComeFolderDtm' => $row[185],
                        'trainingAmount' => $row[186],
                        'societyServiceamount' => $row[187],
                        'totalAmount' => $row[188],
                        'gstAmount' => $row[189],
                        'totalfranchisegstFund' => $row[190],
                        'legalCharges' => $row[191],
                        'legalChargesdue' => $row[192],
                        'totalgstCharges' => $row[193],
                        'totalPaidamount' => $row[194],
                        'dueFranchiseamt' => $row[195],
                        'kitCharges' => $row[196],
                        'numinitialKit' => $row[197],
                        'totalKitsamt' => $row[198],
                        'kitamtReceived' => $row[199],
                        'dueKitamount' => $row[200],
                        'installationDate' => $row[201],
                        'finaltotalamtDue' => $row[202],
                        'specialRemark' => $row[203],
                        'transporttravCharge' => $row[204],
                        'travelAmount' => $row[205],
                        'receivedtravelAmount' => $row[206],
                        'duetravelAmount' => $row[207],
                        'transportCharges' => $row[208],
                        'transportAmtreceived' => $row[209],
                        'duetransportCharges' => $row[210],
                        'ledgerMarch' => $row[211],
                        'ledgerJune' => $row[212],
                        'ledgerSeptember' => $row[213],
                        'ledgerDecember' => $row[214],
                        'reminderAMCStatus1Dec' => $row[215],
                        'reminderAMCStatus10Dec' => $row[216],
                        'reminderAMCStatus15Dec' => $row[217],
                        'reminderAMCStatus19Dec' => $row[218],
                        'reminderAMCStatus20Dec' => $row[219],
                        'RemarkforAMCmail' => $row[220],
                        'InvoiceAMCClearance' => $row[221],
                        'PenaltyMailnoncle' => $row[222],
                        'invoiceNumberAll' => $row[223],
                        'upgradeUptoclass' => $row[224],
                        'branchStatus' => $row[225],
                        'undertakingAck' => $row[226],
                        'optOnlineMarketing' => $row[227],
                        'DetailsReceiptmail' => $row[228],
                        'ConfBrinsScheduledemail' => $row[229],
                        'BrinsScheduledemail' => $row[230],
                        'videoFeedbackbr' => $row[231],
                        'writtenFeedbackbr' => $row[232],
                        'ShoppinPortTraining' => $row[233],
                        'insmatDispatchdate' => $row[234],
                        'Materialrecdate' => $row[235],
                        'BrinsScheduleddate' => $row[236],
                        'brInstalationRemark' => $row[237],
                        'ShoppinPortSharedDate' => $row[238],
                        'ShoppinPortTrainingDate' => $row[239]
                        /*'ShoppinPortRemark' => $row[240],
                        'returnItems' => $row[241],
                        'modeOfDespatch' => $row[242],
                        'NumOfBoxes' => $row[243],
                        'PoDNum' => $row[244],
                        'SpecificGiftOffer' => $row[245],
                        'ConfBrInsOverPhone' => $row[246],
                        'shortComming' => $row[247]*/
                        //'solutionShortComming' => $row[248]
                    );
                    $result = $this->bm->insertCsvFile($data);
                    $this->addLegalDocuments($result);
                }
                // $result = $this->bm->insertCsvFile($data);
                // $this->addLegalDocuments($result);
                // if($result > 0) {
                    
                //     $this->session->set_flashdata('success', 'File upload successfully');
                // } else {
                //     $this->session->set_flashdata('error', 'File failed');
                // }
            }
            
            // $this->insertFile($csv_file_path);

            unlink($csv_file_path);
            $this->session->set_flashdata('success', 'File upload successfully');
            redirect('branches/branchesListing');
        }
                
                
    }
    /*** This function is used to import csv file*/

    private function insertFile($file_path) {
        
        
    }

    
}

?>