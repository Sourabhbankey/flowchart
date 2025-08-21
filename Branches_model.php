<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class : Branches_model (Branches Model)
 * Branches model class to get to handle branches related data 
 * @author : Ashish Singh
 * @version : 1.5
 * @since : 18 Jun 2022
 */
class Branches_model extends CI_Model
{
    /**
     * This function is used to get the branches listing count
     * @param string $searchText : This is optional search text
     * @return number $count : This is row count
     */
    function branchesListingCount($searchText, $role, $UserID)
    {
        $this->db->select('BaseTbl.branchesId, BaseTbl.applicantName, BaseTbl.branchEmail, BaseTbl.mobile, BaseTbl.branchcityName, BaseTbl.branchState, BaseTbl.branchSalesDoneby, BaseTbl.branchAmountReceived, BaseTbl.branchFranchiseAssigned, BaseTbl.branchAddress, BaseTbl.permanentAddress,  BaseTbl.franchiseNumber, BaseTbl.franchiseName, BaseTbl.typeBranch, BaseTbl.currentStatus, BaseTbl.bookingDate, BaseTbl.licenseNumber, BaseTbl.licenseSharedon, BaseTbl.validFromDate, BaseTbl.validTillDate, BaseTbl.branchLocation, BaseTbl.adminName, BaseTbl.adminContactNum, BaseTbl.additionalNumber, BaseTbl.officialEmailID, BaseTbl.personalEmailId, BaseTbl.biometricInstalled, BaseTbl.biometricRemark, BaseTbl.biometricInstalledDate, BaseTbl.camaraInstalled, BaseTbl.camaraRemark, BaseTbl.camaraInstalledDate, BaseTbl.eduMetaAppTraining, BaseTbl.AppTrainingRemark, BaseTbl.AppTrainingRemarkDate, BaseTbl.congratulationsImg, BaseTbl.brimguploadedFBStatus, BaseTbl.brimguploadedFBDate, BaseTbl.brimguploadedInstaStatus, BaseTbl.brimguploadedInstaDate, BaseTbl.admissionOpenimgStatus, BaseTbl.staffHiringimgStatus, BaseTbl.newsletterMarch, BaseTbl.newsletterJune, BaseTbl.newsletterSeptember, BaseTbl.newsletterDecember, BaseTbl.OBirthDayImgStatus, BaseTbl.OBirthDayImgSharedDtm, BaseTbl.OwnerAnnImgStatus, BaseTbl.OwnerAnnImgSharedDtm, BaseTbl.facebookPageStatus, BaseTbl.facebookPageLink, BaseTbl.facebookPageRemark, BaseTbl.googleMapLoc, BaseTbl.googleMapLocLink, BaseTbl.googleMapLocRemark, BaseTbl.instagramPageStatus, BaseTbl.instagramPageID, BaseTbl.instagramPageRemark, BaseTbl.jdPageStatus, BaseTbl.jdPageID, BaseTbl.jdPageRemark, BaseTbl.tweetPageStatus, BaseTbl.tweetPageID, BaseTbl.tweetPageRemark, BaseTbl.digiMarkCost, BaseTbl.digiMarkStartDtm, BaseTbl.digiMarkEndDtm, BaseTbl.digiMarkReamrk, BaseTbl.insfeedvideoUplodFB, BaseTbl.insfeedvideoUplodYoutube, BaseTbl.insfeedvideoUplodInsta, BaseTbl.branchLocAddressPremise, BaseTbl.addOfFranchise, BaseTbl.gstNumber, BaseTbl.undertakingCommitmentSupport, BaseTbl.amcAmount, BaseTbl.invoiceNumber, BaseTbl.agreementTenure, BaseTbl.salesExecutive, BaseTbl.salesTeamlead, BaseTbl.Manual1, BaseTbl.Manual2, BaseTbl.Manual3, BaseTbl.Reference, BaseTbl.installationTentativeDate, BaseTbl.formsDocumentsCompleted, BaseTbl.setUpInstallation, BaseTbl.branchAnniversaryDate, BaseTbl.admissionCracked, BaseTbl.teacherRecruitment, BaseTbl.pgDecidedFee, BaseTbl.nurseryDecidedFee, BaseTbl.KG1DecidedFee, BaseTbl.KG2DecidedFee, BaseTbl.feeSharedStatus, BaseTbl.feesRemark, BaseTbl.addmissionPG, BaseTbl.addmissionNursary, BaseTbl.addmissionKg1, BaseTbl.addmissionKg2, BaseTbl.addmission1st, BaseTbl.addmission2nd, BaseTbl.totalAddmission, BaseTbl.addmissionCounselor, BaseTbl.lastDiscussaddmission, BaseTbl.addmissionSheetlink, BaseTbl.dateexlSheetshared, BaseTbl.lastInteractiondate, BaseTbl.lastDiscussionby, BaseTbl.lastInteractioncomment, BaseTbl.agreementDraftdate, BaseTbl.branchLandline, BaseTbl.additionalName, BaseTbl.finalPaydeadline, BaseTbl.BranchSpecialNoteSales, BaseTbl.completeFranchiseAmt, BaseTbl.confirmationAmt33kGST, BaseTbl.happinessLevelbranch, BaseTbl.DesignsPromotional, BaseTbl.DesignsPromotionalRemark, BaseTbl.BranchSpecialNote, BaseTbl.OwnerAnniversery, BaseTbl.welcomeCall, BaseTbl.welcomeMail, BaseTbl.whatsappGroup, BaseTbl.whatsappGroupRemark, BaseTbl.whatsappGroupdate, BaseTbl.interactionMeeting, BaseTbl.interactionMeetingRemark, BaseTbl.undertakingCommitment, BaseTbl.onboardingForm, BaseTbl.onboardingFormReceived, BaseTbl.onboardingFormRemark, BaseTbl.installationRequirementmail, BaseTbl.installationRequirementmailRemark, BaseTbl.agreementDraftReceiveddate, BaseTbl.compFileSubmit, BaseTbl.fileCLoserDate, BaseTbl.branchStatusRemark, BaseTbl.finalAgreementShared, BaseTbl.officialemailshared,  BaseTbl.inaugurationDate, BaseTbl.classroomDecoration, BaseTbl.movieClub, BaseTbl.referEarn, BaseTbl.teacherInteraction, BaseTbl.teacherInterview, BaseTbl.pongalWorkshop, BaseTbl.sankrantiWorkshop, BaseTbl.republicDayWorkshop, BaseTbl.bridgeCourseCounselling, BaseTbl.bulletinBoard, BaseTbl.bridgeCourse, BaseTbl.settlersProgram, BaseTbl.jollyPhonic, BaseTbl.academicsMeetings, BaseTbl.timeDisclipineemail, BaseTbl.uniformDisclipineemail, BaseTbl.curiculumnShared, BaseTbl.holidaEventlisting, BaseTbl.sharingAssessmentpapers, BaseTbl.assessmentSharingemail, BaseTbl.PTMscheduledate, BaseTbl.shadowPuppet, BaseTbl.monthlyEventtraining, BaseTbl.summertCampdate, BaseTbl.winterCampdate, BaseTbl.offerName, BaseTbl.offerPlanname, BaseTbl.discountAmount, BaseTbl.finalAmount, BaseTbl.legalChargesSales, BaseTbl.brSetupinsChargSales, BaseTbl.numInialKitSales, BaseTbl.franchiseTenure, BaseTbl.welComeFolderStatus, BaseTbl.welComeFolderDtm, BaseTbl.trainingAmount, BaseTbl.societyServiceamount, BaseTbl.totalAmount, BaseTbl.gstAmount, BaseTbl.totalfranchisegstFund, BaseTbl.legalCharges, BaseTbl.legalChargesdue, BaseTbl.totalgstCharges, BaseTbl.totalPaidamount, BaseTbl.dueFranchiseamt, BaseTbl.kitCharges, BaseTbl.numinitialKit, BaseTbl.totalKitsamt, BaseTbl.kitamtReceived, BaseTbl.dueKitamount, BaseTbl.installationDate, BaseTbl.finaltotalamtDue, BaseTbl.specialRemark, BaseTbl.transporttravCharge, BaseTbl.brsetupinstachargReceived, BaseTbl.brsetupinstachargDue, BaseTbl.travelAmount, BaseTbl.receivedtravelAmount, BaseTbl.duetravelAmount, BaseTbl.transportCharges, BaseTbl.transportAmtreceived, BaseTbl.duetransportCharges, BaseTbl.ledgerMarch, BaseTbl.ledgerJune, BaseTbl.ledgerSeptember, BaseTbl.ledgerDecember, BaseTbl.reminderAMCStatus1Dec, BaseTbl.reminderAMCStatus10Dec, BaseTbl.reminderAMCStatus15Dec, BaseTbl.reminderAMCStatus19Dec, BaseTbl.reminderAMCStatus20Dec, BaseTbl.RemarkforAMCmail, BaseTbl.InvoiceAMCClearance, BaseTbl.PenaltyMailnoncle, BaseTbl.invoiceNumberAll, BaseTbl.upgradeUptoclass, BaseTbl.branchStatus, BaseTbl.brInstallationStatus, BaseTbl.undertakingAck, BaseTbl.optOnlineMarketing, BaseTbl.insmatDispatchdate, BaseTbl.DetailsReceiptmail, BaseTbl.ConfBrinsScheduledemail, BaseTbl.Materialrecdate, BaseTbl.BrinsScheduleddate, BaseTbl.BrinsScheduledemail, BaseTbl.brInstalationRemark, BaseTbl.videoFeedbackbr, BaseTbl.writtenFeedbackbr, BaseTbl.ShoppinPortSharedDate, BaseTbl.ShoppinPortTraining, BaseTbl.ShoppinPortTrainingDate, BaseTbl.ShoppinPortRemark, BaseTbl.returnItems, BaseTbl.modeOfDespatch, BaseTbl.NumOfBoxes, BaseTbl.PoDNum, BaseTbl.SpecificGiftOffer, BaseTbl.ConfBrInsOverPhone, BaseTbl.shortComming, BaseTbl.solutionShortComming, BaseTbl.customWebsiteLink, BaseTbl.trainingcat, BaseTbl.createdDtm,BaseTbl.IntroductionDate, BaseTbl.Pre_marketingDate, BaseTbl.Admin_OrientationDate, BaseTbl.Inauguration_Refer_and_EarnDate, BaseTbl.Classroom_decorationDate, BaseTbl.Movie_clubDate, BaseTbl.Fee_structureDate, BaseTbl.Day_careDate, BaseTbl.ToddlerDate, BaseTbl.pG_April_JuneDate, BaseTbl.pG_JulyDate, BaseTbl.pG_AugustDate, BaseTbl.pG_SeptemberDate, BaseTbl.pG_OctoberDate, BaseTbl.pG_NovemberDate, BaseTbl.pG_DecemberDate, BaseTbl.pG_JanuaryDate, BaseTbl.pG_FebruaryDate, BaseTbl.pG_MarchDate, BaseTbl.NurseryBook_1_Date, BaseTbl.NurseryBook_2_Date, BaseTbl.NurseryBook_3_Date, BaseTbl.NurseryBook_4_Date, BaseTbl.NurseryBook_5_Date, BaseTbl.NurseryBook_6_Date, BaseTbl.NurseryBook_7_Date, BaseTbl.NurseryBook_8_Date, BaseTbl.NurseryBook_9_Date, BaseTbl.KG1Book_1_Date, BaseTbl.KG1Book_2_Date, BaseTbl.KG1Book_3_Date, BaseTbl.KG1Book_4_Date, BaseTbl.KG1Book_5_Date, BaseTbl.KG1Book_6_Date, BaseTbl.KG1Book_7_Date, BaseTbl.KG1Book_8_Date, BaseTbl.KG1Book_9_Date, BaseTbl.KG2Book_1_Date, BaseTbl.KG2Book_2_Date, BaseTbl.KG2Book_3_Date, BaseTbl.KG2Book_4_Date, BaseTbl.KG2Book_5_Date, BaseTbl.KG2Book_6_Date, BaseTbl.KG2Book_7_Date, BaseTbl.KG2Book_8_Date, BaseTbl.KG2Book_9_Date, BaseTbl.eventCelebration_April_JuneDate, BaseTbl.eventCelebration_JulyDate, BaseTbl.eventCelebration_AugustDate, BaseTbl.eventCelebration_SeptemberDate, BaseTbl.eventCelebration_OctoberDate, BaseTbl.eventCelebration_NovemberDate, BaseTbl.eventCelebration_DecemberDate, BaseTbl.eventCelebration_JanuaryDate, BaseTbl.eventCelebration_FebruaryDate, BaseTbl.eventCelebration_MarchDate, BaseTbl.Workshop_1Date, BaseTbl.Workshop_2Date, BaseTbl.Workshop_3Date, BaseTbl.Workshop_4Date, BaseTbl.Workshop_5Date, BaseTbl.Workshop_6Date, BaseTbl.Workshop_7Date, BaseTbl.others_Settlers_program_Date, BaseTbl.others_Circle_time_Date, BaseTbl.others_Interview_Date, BaseTbl.others_Academic_Overview_Date, BaseTbl.others_Teacher_interaction_Date, BaseTbl.others_Assessment_Date, BaseTbl.others_PTM_Date, BaseTbl.others_Summer_camp_Date, BaseTbl.others_Winter_Camp_Date, BaseTbl.others_Aayam_Date, BaseTbl.others_Sports_day_Date,BaseTbl.others_midterm_session,BaseTbl.others_bridgecourse');
        $this->db->from('tbl_branches as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.applicantName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        if (!in_array($role, [1,2,14])) {
            $conditions = "(branchFranchiseAssigned=".$UserID." OR branchFranchiseAssignedDesigning=".$UserID." OR branchFranchiseAssignedLegalDepartment=".$UserID." OR branchFrAssignedAccountsDepartment=".$UserID." OR branchFrAssignedDispatchDepartment=".$UserID." OR branchFrAssignedAdmintrainingDepartment=".$UserID." OR branchFrAssignedAdmissionDepartment=".$UserID." OR branchFrAssignedMaterialDepartment=".$UserID." OR branchFrAssignedDigitalDepartment=".$UserID." OR  branchFrAssignedTrainingDepartment=".$UserID." OR  branchFrAssignedSocialmediaDepartment=".$UserID.")";

            $this->db->where($conditions);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $query = $this->db->get();
        
        return $query->num_rows();
    }
    
    /**
     * This function is used to get the branches listing count
     * @param string $searchText : This is optional search text
     * @param number $page : This is pagination offset
     * @param number $segment : This is pagination limit
     * @return array $result : This is result
     */
    function branchesListing($searchText, $page, $segment, $role, $UserID)
    {
        $this->db->select('BaseTbl.branchesId, BaseTbl.applicantName, BaseTbl.branchEmail, BaseTbl.mobile, BaseTbl.branchcityName, BaseTbl.branchState, BaseTbl.branchSalesDoneby, BaseTbl.branchAmountReceived, userTbl.name as branchFranchiseAssigned, BaseTbl.branchAddress, BaseTbl.permanentAddress,  BaseTbl.franchiseNumber, BaseTbl.franchiseName, BaseTbl.typeBranch, BaseTbl.currentStatus, BaseTbl.bookingDate, BaseTbl.licenseNumber, BaseTbl.licenseSharedon, BaseTbl.validFromDate, BaseTbl.validTillDate, BaseTbl.branchLocation, BaseTbl.adminName, BaseTbl.adminContactNum, BaseTbl.additionalNumber, BaseTbl.officialEmailID, BaseTbl.personalEmailId, BaseTbl.biometricInstalled, BaseTbl.biometricRemark, BaseTbl.biometricInstalledDate, BaseTbl.camaraInstalled, BaseTbl.camaraRemark, BaseTbl.camaraInstalledDate, BaseTbl.eduMetaAppTraining, BaseTbl.AppTrainingRemark, BaseTbl.AppTrainingRemarkDate, BaseTbl.congratulationsImg, BaseTbl.brimguploadedFBStatus, BaseTbl.brimguploadedFBDate, BaseTbl.brimguploadedInstaStatus, BaseTbl.brimguploadedInstaDate, BaseTbl.admissionOpenimgStatus, BaseTbl.staffHiringimgStatus, BaseTbl.newsletterMarch, BaseTbl.newsletterJune, BaseTbl.newsletterSeptember, BaseTbl.newsletterDecember, BaseTbl.OBirthDayImgStatus, BaseTbl.OBirthDayImgSharedDtm, BaseTbl.OwnerAnnImgStatus, BaseTbl.OwnerAnnImgSharedDtm, BaseTbl.facebookPageStatus, BaseTbl.facebookPageLink, BaseTbl.facebookPageRemark, BaseTbl.googleMapLoc, BaseTbl.googleMapLocLink, BaseTbl.googleMapLocRemark, BaseTbl.instagramPageStatus, BaseTbl.instagramPageID, BaseTbl.instagramPageRemark, BaseTbl.jdPageStatus, BaseTbl.jdPageID, BaseTbl.jdPageRemark, BaseTbl.tweetPageStatus, BaseTbl.tweetPageID, BaseTbl.tweetPageRemark, BaseTbl.digiMarkCost, BaseTbl.digiMarkStartDtm, BaseTbl.digiMarkEndDtm, BaseTbl.digiMarkReamrk, BaseTbl.insfeedvideoUplodFB, BaseTbl.insfeedvideoUplodYoutube, BaseTbl.insfeedvideoUplodInsta, BaseTbl.branchLocAddressPremise, BaseTbl.addOfFranchise, BaseTbl.gstNumber, BaseTbl.undertakingCommitmentSupport, BaseTbl.amcAmount, BaseTbl.invoiceNumber, BaseTbl.agreementTenure, BaseTbl.salesExecutive, BaseTbl.salesTeamlead, BaseTbl.Manual1, BaseTbl.Manual2, BaseTbl.Manual3, BaseTbl.Reference, BaseTbl.installationTentativeDate, BaseTbl.formsDocumentsCompleted, BaseTbl.setUpInstallation, BaseTbl.branchAnniversaryDate, BaseTbl.admissionCracked, BaseTbl.teacherRecruitment, BaseTbl.pgDecidedFee, BaseTbl.nurseryDecidedFee, BaseTbl.KG1DecidedFee, BaseTbl.KG2DecidedFee, BaseTbl.feeSharedStatus, BaseTbl.feesRemark, BaseTbl.addmissionPG, BaseTbl.addmissionNursary, BaseTbl.addmissionKg1, BaseTbl.addmissionKg2, BaseTbl.addmission1st, BaseTbl.addmission2nd, BaseTbl.totalAddmission, BaseTbl.addmissionCounselor, BaseTbl.lastDiscussaddmission, BaseTbl.addmissionSheetlink, BaseTbl.dateexlSheetshared, BaseTbl.lastInteractiondate, BaseTbl.lastDiscussionby, BaseTbl.lastInteractioncomment, BaseTbl.agreementDraftdate, BaseTbl.branchLandline, BaseTbl.additionalName, BaseTbl.finalPaydeadline, BaseTbl.BranchSpecialNoteSales, BaseTbl.completeFranchiseAmt, BaseTbl.confirmationAmt33kGST, BaseTbl.happinessLevelbranch, BaseTbl.DesignsPromotional, BaseTbl.DesignsPromotionalRemark, BaseTbl.BranchSpecialNote, BaseTbl.OwnerAnniversery, BaseTbl.welcomeCall, BaseTbl.welcomeMail, BaseTbl.whatsappGroup, BaseTbl.whatsappGroupRemark, BaseTbl.whatsappGroupdate, BaseTbl.interactionMeeting, BaseTbl.interactionMeetingRemark, BaseTbl.undertakingCommitment, BaseTbl.onboardingForm, BaseTbl.onboardingFormReceived, BaseTbl.onboardingFormRemark, BaseTbl.installationRequirementmail, BaseTbl.installationRequirementmailRemark, BaseTbl.agreementDraftReceiveddate, BaseTbl.compFileSubmit, BaseTbl.fileCLoserDate, BaseTbl.branchStatusRemark, BaseTbl.officialemailshared, BaseTbl.finalAgreementShared, BaseTbl.officialemailshared,
         BaseTbl.inaugurationDate, BaseTbl.classroomDecoration, BaseTbl.movieClub, BaseTbl.referEarn, BaseTbl.teacherInteraction, BaseTbl.teacherInterview, BaseTbl.pongalWorkshop, BaseTbl.sankrantiWorkshop, BaseTbl.republicDayWorkshop, BaseTbl.bridgeCourseCounselling, BaseTbl.bulletinBoard, BaseTbl.bridgeCourse, BaseTbl.settlersProgram, BaseTbl.jollyPhonic, BaseTbl.academicsMeetings, BaseTbl.timeDisclipineemail, BaseTbl.uniformDisclipineemail, BaseTbl.curiculumnShared, BaseTbl.holidaEventlisting, BaseTbl.sharingAssessmentpapers, BaseTbl.assessmentSharingemail, BaseTbl.PTMscheduledate, BaseTbl.shadowPuppet, BaseTbl.monthlyEventtraining, BaseTbl.summertCampdate, BaseTbl.winterCampdate, BaseTbl.offerName, BaseTbl.offerPlanname, BaseTbl.discountAmount, BaseTbl.finalAmount, BaseTbl.legalChargesSales, BaseTbl.brSetupinsChargSales, BaseTbl.numInialKitSales, BaseTbl.franchiseTenure, BaseTbl.welComeFolderStatus, BaseTbl.welComeFolderDtm, BaseTbl.trainingAmount, BaseTbl.societyServiceamount, BaseTbl.totalAmount, BaseTbl.gstAmount, BaseTbl.totalfranchisegstFund, BaseTbl.legalCharges, BaseTbl.legalChargesdue, BaseTbl.totalgstCharges, BaseTbl.totalPaidamount, BaseTbl.dueFranchiseamt, BaseTbl.kitCharges, BaseTbl.numinitialKit, BaseTbl.totalKitsamt, BaseTbl.kitamtReceived, BaseTbl.dueKitamount, BaseTbl.installationDate, BaseTbl.finaltotalamtDue, BaseTbl.specialRemark, BaseTbl.transporttravCharge, BaseTbl.brsetupinstachargReceived, BaseTbl.brsetupinstachargDue, BaseTbl.travelAmount, BaseTbl.receivedtravelAmount, BaseTbl.duetravelAmount, BaseTbl.transportCharges, BaseTbl.transportAmtreceived, BaseTbl.duetransportCharges, BaseTbl.ledgerMarch, BaseTbl.ledgerJune, BaseTbl.ledgerSeptember, BaseTbl.ledgerDecember, BaseTbl.reminderAMCStatus1Dec, BaseTbl.reminderAMCStatus10Dec, BaseTbl.reminderAMCStatus15Dec, BaseTbl.reminderAMCStatus19Dec, BaseTbl.reminderAMCStatus20Dec, BaseTbl.RemarkforAMCmail, BaseTbl.InvoiceAMCClearance, BaseTbl.PenaltyMailnoncle, BaseTbl.invoiceNumberAll, BaseTbl.upgradeUptoclass, BaseTbl.branchStatus, BaseTbl.brInstallationStatus, BaseTbl.undertakingAck, BaseTbl.optOnlineMarketing, BaseTbl.insmatDispatchdate, BaseTbl.DetailsReceiptmail, BaseTbl.ConfBrinsScheduledemail, BaseTbl.Materialrecdate, BaseTbl.BrinsScheduleddate, BaseTbl.BrinsScheduledemail, BaseTbl.brInstalationRemark, BaseTbl.videoFeedbackbr, BaseTbl.writtenFeedbackbr, BaseTbl.ShoppinPortSharedDate, BaseTbl.ShoppinPortTraining, BaseTbl.ShoppinPortTrainingDate, BaseTbl.ShoppinPortRemark, BaseTbl.returnItems, BaseTbl.modeOfDespatch, BaseTbl.NumOfBoxes, BaseTbl.PoDNum, BaseTbl.SpecificGiftOffer, BaseTbl.ConfBrInsOverPhone, BaseTbl.shortComming, BaseTbl.solutionShortComming, BaseTbl.customWebsiteLink,BaseTbl.trainingcat, BaseTbl.createdDtm,BaseTbl.IntroductionDate, BaseTbl.Pre_marketingDate, BaseTbl.Admin_OrientationDate, BaseTbl.Inauguration_Refer_and_EarnDate, BaseTbl.Classroom_decorationDate, BaseTbl.Movie_clubDate, BaseTbl.Fee_structureDate, BaseTbl.Day_careDate, BaseTbl.ToddlerDate, BaseTbl.pG_April_JuneDate, BaseTbl.pG_JulyDate, BaseTbl.pG_AugustDate, BaseTbl.pG_SeptemberDate, BaseTbl.pG_OctoberDate, BaseTbl.pG_NovemberDate, BaseTbl.pG_DecemberDate, BaseTbl.pG_JanuaryDate, BaseTbl.pG_FebruaryDate, BaseTbl.pG_MarchDate, BaseTbl.NurseryBook_1_Date, BaseTbl.NurseryBook_2_Date, BaseTbl.NurseryBook_3_Date, BaseTbl.NurseryBook_4_Date, BaseTbl.NurseryBook_5_Date, BaseTbl.NurseryBook_6_Date, BaseTbl.NurseryBook_7_Date, BaseTbl.NurseryBook_8_Date, BaseTbl.NurseryBook_9_Date, BaseTbl.KG1Book_1_Date, BaseTbl.KG1Book_2_Date, BaseTbl.KG1Book_3_Date, BaseTbl.KG1Book_4_Date, BaseTbl.KG1Book_5_Date, BaseTbl.KG1Book_6_Date, BaseTbl.KG1Book_7_Date, BaseTbl.KG1Book_8_Date, BaseTbl.KG1Book_9_Date, BaseTbl.KG2Book_1_Date, BaseTbl.KG2Book_2_Date, BaseTbl.KG2Book_3_Date, BaseTbl.KG2Book_4_Date, BaseTbl.KG2Book_5_Date, BaseTbl.KG2Book_6_Date, BaseTbl.KG2Book_7_Date, BaseTbl.KG2Book_8_Date, BaseTbl.KG2Book_9_Date, BaseTbl.eventCelebration_April_JuneDate, BaseTbl.eventCelebration_JulyDate, BaseTbl.eventCelebration_AugustDate, BaseTbl.eventCelebration_SeptemberDate, BaseTbl.eventCelebration_OctoberDate, BaseTbl.eventCelebration_NovemberDate, BaseTbl.eventCelebration_DecemberDate, BaseTbl.eventCelebration_JanuaryDate, BaseTbl.eventCelebration_FebruaryDate, BaseTbl.eventCelebration_MarchDate, BaseTbl.Workshop_1Date, BaseTbl.Workshop_2Date, BaseTbl.Workshop_3Date, BaseTbl.Workshop_4Date, BaseTbl.Workshop_5Date, BaseTbl.Workshop_6Date, BaseTbl.Workshop_7Date, BaseTbl.others_Settlers_program_Date, BaseTbl.others_Circle_time_Date, BaseTbl.others_Interview_Date, BaseTbl.others_Academic_Overview_Date, BaseTbl.others_Teacher_interaction_Date, BaseTbl.others_Assessment_Date, BaseTbl.others_PTM_Date, BaseTbl.others_Summer_camp_Date, BaseTbl.others_Winter_Camp_Date, BaseTbl.others_Aayam_Date, BaseTbl.others_Sports_day_Date,BaseTbl.others_midterm_session,BaseTbl.others_bridgecourse');
        $this->db->from('tbl_branches as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'BaseTbl.branchFranchiseAssigned = userTbl.userId', 'LEFT');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.applicantName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        if (!in_array($role, [1,2,14])) {
            $conditions = "(branchFranchiseAssigned=".$UserID." OR branchFranchiseAssignedDesigning=".$UserID." OR branchFranchiseAssignedLegalDepartment=".$UserID." OR branchFrAssignedAccountsDepartment=".$UserID." OR branchFrAssignedDispatchDepartment=".$UserID." OR branchFrAssignedAdmintrainingDepartment=".$UserID." OR branchFrAssignedAdmissionDepartment=".$UserID." OR branchFrAssignedMaterialDepartment=".$UserID." OR branchFrAssignedDigitalDepartment=".$UserID." OR branchFrAssignedTrainingDepartment=".$UserID." OR branchFrAssignedSocialmediaDepartment=".$UserID.")";

            $this->db->where($conditions);
        }
        $this->db->where('BaseTbl.isDeleted', 0);
        $this->db->order_by('BaseTbl.branchesId', 'DESC');
        $this->db->limit($page, $segment);
        $query = $this->db->get();
        
        $result = $query->result();        
        return $result;
    }
    
    /**
     * This function is used to add new booking to system
     * @return number $insert_id : This is last inserted id
     */
   /** function addNewBranches($branchesInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_branches', $branchesInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    */
	/*   public function addNewBranches($branchesInfo)
{
    $this->db->trans_start();

    $this->db->insert('tbl_branches', $branchesInfo);
    $branch_insert_id = $this->db->insert_id();  // Get the inserted branch ID

   
         $amcInfo = array(
                'franchiseNumber' => $branchesInfo['franchiseNumber'],
                'franchiseName' => $branchesInfo['franchiseName'], 
                'branchLocation' => $branchesInfo['branchLocation'],
                'branchState' => $branchesInfo['branchState'],// Insert franchise number
        
             );
         //print_r($amcInfo);exit;
             $this->db->insert('tbl_amc', $amcInfo);

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return false;
            }
             return $branch_insert_id;
}*/


//code done by yashi 20/11


public function addNewBranches($branchesInfo)
{
    $this->db->trans_start();

    // 1. Insert into tbl_branches
    $this->db->insert('tbl_branches', $branchesInfo);
    $branch_insert_id = $this->db->insert_id();

    // 2. Generate franchiseNumber
    $franchiseNumber = 'EEIPL' . $branch_insert_id;
    $this->db->where('branchesId', $branch_insert_id);
    $this->db->update('tbl_branches', ['franchiseNumber' => $franchiseNumber]);

    // 3. Always insert into tbl_amc if not already there
    $this->db->select('franchiseNumber');
    $this->db->from('tbl_amc');
    $this->db->where('franchiseNumber', $franchiseNumber);
    $amcExists = $this->db->get()->num_rows();

    if ($amcExists == 0) {
        $amcInfo = [
    'franchiseNumber' => $franchiseNumber,
    'franchiseName' => $branchesInfo['franchiseName'],
    'branchState' => $branchesInfo['branchState'],
    'branchcityName' => $branchesInfo['branchcityName'] ?? '',
    'brspFranchiseAssigned' => $branchesInfo['branchFranchiseAssigned'] ?? 'Not Assigned',
    'currentStatus' => $branchesInfo['currentStatus'] ?? '',
];
        $this->db->insert('tbl_amc', $amcInfo);
    }

    // 4. Send notifications
   

    // $assignedUsers = $this->Notification_model->get_assigned_user_by_branch($branch_insert_id);
    // foreach ($assignedUsers as $user) {
    //     $this->Notification_model->add_branch_notification(
    //         $user['userId'], 
    //         'You have been assigned to branch: ' . $branchesInfo['franchiseName'],
    //         $branch_insert_id
    //     );
    // }

    $this->db->trans_complete();

    return $this->db->trans_status() ? $branch_insert_id : false;
}




    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getBranchesInfo($branchesId)
    {
        $this->db->select('branchesId, applicantName, mobile, branchcityName, branchState, branchSalesDoneby, branchAmountReceived, branchFranchiseAssigned, branchFranchiseAssignedLegalDepartment, branchFranchiseAssignedDesigning, branchFrAssignedAccountsDepartment, branchFrAssignedDispatchDepartment, branchFrAssignedAdmintrainingDepartment, branchFrAssignedAdmissionDepartment, branchFrAssignedMaterialDepartment, branchFrAssignedDigitalDepartment, branchFrAssignedTrainingDepartment, branchFrAssignedSocialmediaDepartment, branchAddress, permanentAddress, branchEmail, franchiseNumber, franchiseName, typeBranch, currentStatus, bookingDate, licenseNumber, licenseSharedon, validFromDate, validTillDate, branchLocation, adminName, adminContactNum, additionalNumber, officialEmailID, personalEmailId, biometricInstalled, biometricRemark, biometricInstalledDate, camaraInstalled, camaraRemark, camaraInstalledDate, eduMetaAppTraining, AppTrainingRemark, AppTrainingRemarkDate, congratulationsImg, brimguploadedFBStatus, brimguploadedFBDate, brimguploadedInstaStatus, brimguploadedInstaDate, admissionOpenimgStatus, staffHiringimgStatus, newsletterMarch, newsletterJune, newsletterSeptember, newsletterDecember, OBirthDayImgStatus, OBirthDayImgSharedDtm, OwnerAnnImgStatus, OwnerAnnImgSharedDtm, facebookPageStatus, facebookPageLink, facebookPageRemark, googleMapLoc, googleMapLocLink, googleMapLocRemark, instagramPageStatus, instagramPageID, instagramPageRemark, jdPageStatus, jdPageID, jdPageRemark, tweetPageStatus, tweetPageID, tweetPageRemark, digiMarkCost, digiMarkStartDtm, digiMarkEndDtm, digiMarkReamrk, insfeedvideoUplodFB, insfeedvideoUplodYoutube, insfeedvideoUplodInsta, branchLocAddressPremise, addOfFranchise, gstNumber, undertakingCommitmentSupport, amcAmount, invoiceNumber, agreementTenure, salesExecutive, salesTeamlead, Manual1, Manual2, Manual3, Reference, installationTentativeDate, formsDocumentsCompleted, setUpInstallation, branchAnniversaryDate, admissionCracked, teacherRecruitment, pgDecidedFee, nurseryDecidedFee, KG1DecidedFee, KG2DecidedFee, feeSharedStatus, feesRemark, addmissionPG, addmissionNursary, addmissionKg1, addmissionKg2, addmission1st, addmission2nd, totalAddmission, addmissionCounselor, lastDiscussaddmission, addmissionSheetlink, dateexlSheetshared, lastInteractiondate, lastDiscussionby, lastInteractioncomment, agreementDraftdate, branchLandline, additionalName, finalPaydeadline, BranchSpecialNoteSales, completeFranchiseAmt, confirmationAmt33kGST, happinessLevelbranch, DesignsPromotional, DesignsPromotionalRemark, BranchSpecialNote, OwnerAnniversery, welcomeCall, welcomeMail, whatsappGroup, whatsappGroupRemark, whatsappGroupdate, interactionMeeting, interactionMeetingRemark, undertakingCommitment, onboardingForm, onboardingFormReceived, onboardingFormRemark, installationRequirementmail, installationRequirementmailRemark,  finalAgreementShared, agreementDraftReceiveddate, compFileSubmit, fileCLoserDate, branchStatusRemark, officialemailshared, 
         inaugurationDate, classroomDecoration, movieClub, referEarn, teacherInteraction, teacherInterview, pongalWorkshop, sankrantiWorkshop, republicDayWorkshop, bridgeCourseCounselling, bulletinBoard, bridgeCourse, settlersProgram, jollyPhonic, academicsMeetings, timeDisclipineemail, uniformDisclipineemail, curiculumnShared, holidaEventlisting, sharingAssessmentpapers, assessmentSharingemail, PTMscheduledate, shadowPuppet, monthlyEventtraining, summertCampdate, winterCampdate, offerName, offerPlanname, discountAmount, finalAmount, legalChargesSales, brSetupinsChargSales, numInialKitSales, franchiseTenure, welComeFolderStatus, welComeFolderDtm, trainingAmount, societyServiceamount, totalAmount, gstAmount, totalfranchisegstFund, legalCharges, legalChargesdue, totalgstCharges, totalPaidamount, dueFranchiseamt, kitCharges, numinitialKit, totalKitsamt, kitamtReceived, dueKitamount, installationDate, finaltotalamtDue, specialRemark, transporttravCharge, brsetupinstachargReceived, brsetupinstachargDue, travelAmount, receivedtravelAmount, duetravelAmount, transportCharges, transportAmtreceived, duetransportCharges, ledgerMarch, ledgerJune, ledgerSeptember, ledgerDecember, reminderAMCStatus1Dec, reminderAMCStatus10Dec, reminderAMCStatus15Dec, reminderAMCStatus19Dec, reminderAMCStatus20Dec, RemarkforAMCmail, InvoiceAMCClearance, PenaltyMailnoncle, invoiceNumberAll, upgradeUptoclass, branchStatus, brInstallationStatus, undertakingAck, optOnlineMarketing, insmatDispatchdate, DetailsReceiptmail, ConfBrinsScheduledemail, Materialrecdate, BrinsScheduleddate, BrinsScheduledemail, brInstalationRemark, videoFeedbackbr, writtenFeedbackbr, ShoppinPortSharedDate, ShoppinPortTraining, ShoppinPortTrainingDate, ShoppinPortRemark, returnItems, modeOfDespatch, NumOfBoxes, PoDNum, SpecificGiftOffer, ConfBrInsOverPhone, shortComming, solutionShortComming, customWebsiteLink,franchiseName,LedgerMonthDrop,LedgerYear,trainingcat, IntroductionDate, Pre_marketingDate, Admin_OrientationDate,
    Inauguration_Refer_and_EarnDate,
    Classroom_decorationDate,
    Movie_clubDate,
    Fee_structureDate,
    Day_careDate,
    ToddlerDate,
    pG_April_JuneDate,
    pG_JulyDate,
    pG_AugustDate,
    pG_SeptemberDate,
    pG_OctoberDate,
    pG_NovemberDate,
    pG_DecemberDate,
    pG_JanuaryDate,
    pG_FebruaryDate,
    pG_MarchDate,
    NurseryBook_1_Date,
    NurseryBook_2_Date,
    NurseryBook_3_Date,
    NurseryBook_4_Date,
    NurseryBook_5_Date,
    NurseryBook_6_Date,
    NurseryBook_7_Date,
    NurseryBook_8_Date,
    NurseryBook_9_Date,
    KG1Book_1_Date,
    KG1Book_2_Date,
    KG1Book_3_Date,
    KG1Book_4_Date,
    KG1Book_5_Date,
    KG1Book_6_Date,
    KG1Book_7_Date,
    KG1Book_8_Date,
    KG1Book_9_Date,
    KG2Book_1_Date,
    KG2Book_2_Date,
    KG2Book_3_Date,
    KG2Book_4_Date,
    KG2Book_5_Date,
    KG2Book_6_Date,
    KG2Book_7_Date,
    KG2Book_8_Date,
    KG2Book_9_Date,
    eventCelebration_April_JuneDate,
    eventCelebration_JulyDate,
    eventCelebration_AugustDate,
    eventCelebration_SeptemberDate,
    eventCelebration_OctoberDate,
    eventCelebration_NovemberDate,
    eventCelebration_DecemberDate,
    eventCelebration_JanuaryDate,
    eventCelebration_FebruaryDate,
    eventCelebration_MarchDate,
    Workshop_1Date,
    Workshop_2Date,
    Workshop_3Date,
    Workshop_4Date,
    Workshop_5Date,
    Workshop_6Date,
    Workshop_7Date,
    others_Settlers_program_Date,
    others_Circle_time_Date,
    others_Interview_Date,
    others_Academic_Overview_Date,
    others_Teacher_interaction_Date,
    others_Assessment_Date,
    others_PTM_Date,
    others_Summer_camp_Date,
    others_Winter_Camp_Date,
    others_Aayam_Date,
    others_Sports_day_Date,others_midterm_session,others_bridgecourse');
        $this->db->from('tbl_branches');
        $this->db->where('branchesId', $branchesId);
        $this->db->where('isDeleted', 0);
        $query = $this->db->get();
        
        return $query->row();
    }

        /**
         * This function used to get booking information by id
         * @param number franchiseNumber : This is franchise number
         * @return array $result : This is booking information
         */
        function getBranchesInfoByfranchiseNumber($franchiseNumber)
        {
            $this->db->select('branchesId, applicantName, mobile, branchcityName, branchState, branchSalesDoneby, branchAmountReceived, branchFranchiseAssigned, branchFranchiseAssignedLegalDepartment, branchFranchiseAssignedDesigning, branchFrAssignedAccountsDepartment, branchFrAssignedDispatchDepartment, branchFrAssignedAdmintrainingDepartment, branchFrAssignedAdmissionDepartment, branchFrAssignedMaterialDepartment, branchFrAssignedDigitalDepartment, branchFrAssignedTrainingDepartment, branchFrAssignedSocialmediaDepartment, branchAddress, permanentAddress, branchEmail, franchiseNumber, franchiseName, typeBranch, currentStatus, bookingDate, licenseNumber, licenseSharedon, validFromDate, validTillDate, branchLocation, adminName, adminContactNum, additionalNumber, officialEmailID, personalEmailId, biometricInstalled, biometricRemark, biometricInstalledDate, camaraInstalled, camaraRemark, camaraInstalledDate, eduMetaAppTraining, AppTrainingRemark, AppTrainingRemarkDate, congratulationsImg, brimguploadedFBStatus, brimguploadedFBDate, brimguploadedInstaStatus, brimguploadedInstaDate, admissionOpenimgStatus, staffHiringimgStatus, newsletterMarch, newsletterJune, newsletterSeptember, newsletterDecember, OBirthDayImgStatus, OBirthDayImgSharedDtm, OwnerAnnImgStatus, OwnerAnnImgSharedDtm, facebookPageStatus, facebookPageLink, facebookPageRemark, googleMapLoc, googleMapLocLink, googleMapLocRemark, instagramPageStatus, instagramPageID, instagramPageRemark, jdPageStatus, jdPageID, jdPageRemark, tweetPageStatus, tweetPageID, tweetPageRemark, digiMarkCost, digiMarkStartDtm, digiMarkEndDtm, digiMarkReamrk, insfeedvideoUplodFB, insfeedvideoUplodYoutube, insfeedvideoUplodInsta, branchLocAddressPremise, addOfFranchise, gstNumber, undertakingCommitmentSupport, amcAmount, invoiceNumber, agreementTenure, salesExecutive, salesTeamlead, Manual1, Manual2, Manual3, Reference, installationTentativeDate, formsDocumentsCompleted, setUpInstallation, branchAnniversaryDate, admissionCracked, teacherRecruitment, pgDecidedFee, nurseryDecidedFee, KG1DecidedFee, KG2DecidedFee, feeSharedStatus, feesRemark, addmissionPG, addmissionNursary, addmissionKg1, addmissionKg2, addmission1st, addmission2nd, totalAddmission, addmissionCounselor, lastDiscussaddmission, addmissionSheetlink, dateexlSheetshared, lastInteractiondate, lastDiscussionby, lastInteractioncomment, agreementDraftdate, branchLandline, additionalName, finalPaydeadline, BranchSpecialNoteSales, completeFranchiseAmt, confirmationAmt33kGST, happinessLevelbranch, DesignsPromotional, DesignsPromotionalRemark, BranchSpecialNote, OwnerAnniversery, welcomeCall, welcomeMail, whatsappGroup, whatsappGroupRemark, whatsappGroupdate, interactionMeeting, interactionMeetingRemark, undertakingCommitment, onboardingForm, onboardingFormReceived, onboardingFormRemark, installationRequirementmail, installationRequirementmailRemark,  finalAgreementShared, agreementDraftReceiveddate, compFileSubmit, fileCLoserDate, branchStatusRemark, officialemailshared, inaugurationDate, classroomDecoration, movieClub, referEarn, teacherInteraction, teacherInterview, pongalWorkshop, sankrantiWorkshop, republicDayWorkshop, bridgeCourseCounselling, bulletinBoard, bridgeCourse, settlersProgram, jollyPhonic, academicsMeetings, timeDisclipineemail, uniformDisclipineemail, curiculumnShared, holidaEventlisting, sharingAssessmentpapers, assessmentSharingemail, PTMscheduledate, shadowPuppet, monthlyEventtraining, summertCampdate, winterCampdate, offerName, offerPlanname, discountAmount, finalAmount, legalChargesSales, brSetupinsChargSales, numInialKitSales, franchiseTenure, welComeFolderStatus, welComeFolderDtm, trainingAmount, societyServiceamount, totalAmount, gstAmount, totalfranchisegstFund, legalCharges, legalChargesdue, totalgstCharges, totalPaidamount, dueFranchiseamt, kitCharges, numinitialKit, totalKitsamt, kitamtReceived, dueKitamount, installationDate, finaltotalamtDue, specialRemark, transporttravCharge, brsetupinstachargReceived, brsetupinstachargDue, travelAmount, receivedtravelAmount, duetravelAmount, transportCharges, transportAmtreceived, duetransportCharges, ledgerMarch, ledgerJune, ledgerSeptember, ledgerDecember, reminderAMCStatus1Dec, reminderAMCStatus10Dec, reminderAMCStatus15Dec, reminderAMCStatus19Dec, reminderAMCStatus20Dec, RemarkforAMCmail, InvoiceAMCClearance, PenaltyMailnoncle, invoiceNumberAll, upgradeUptoclass, branchStatus, brInstallationStatus, undertakingAck, optOnlineMarketing, insmatDispatchdate, DetailsReceiptmail, ConfBrinsScheduledemail, Materialrecdate, BrinsScheduleddate, BrinsScheduledemail, brInstalationRemark, videoFeedbackbr, writtenFeedbackbr, ShoppinPortSharedDate, ShoppinPortTraining, ShoppinPortTrainingDate, ShoppinPortRemark, returnItems, modeOfDespatch, NumOfBoxes, PoDNum, SpecificGiftOffer, ConfBrInsOverPhone, shortComming, solutionShortComming, customWebsiteLink,LedgerMonthDrop,LedgerYear,trainingcat, IntroductionDate,
    Pre_marketingDate,
    Admin_OrientationDate,
    Inauguration_Refer_and_EarnDate,
    Classroom_decorationDate,
    Movie_clubDate,
    Fee_structureDate,
    Day_careDate,
    ToddlerDate,
    pG_April_JuneDate,
    pG_JulyDate,
    pG_AugustDate,
    pG_SeptemberDate,
    pG_OctoberDate,
    pG_NovemberDate,
    pG_DecemberDate,
    pG_JanuaryDate,
    pG_FebruaryDate,
    pG_MarchDate,
    NurseryBook_1_Date,
    NurseryBook_2_Date,
    NurseryBook_3_Date,
    NurseryBook_4_Date,
    NurseryBook_5_Date,
    NurseryBook_6_Date,
    NurseryBook_7_Date,
    NurseryBook_8_Date,
    NurseryBook_9_Date,
    KG1Book_1_Date,
    KG1Book_2_Date,
    KG1Book_3_Date,
    KG1Book_4_Date,
    KG1Book_5_Date,
    KG1Book_6_Date,
    KG1Book_7_Date,
    KG1Book_8_Date,
    KG1Book_9_Date,
    KG2Book_1_Date,
    KG2Book_2_Date,
    KG2Book_3_Date,
    KG2Book_4_Date,
    KG2Book_5_Date,
    KG2Book_6_Date,
    KG2Book_7_Date,
    KG2Book_8_Date,
    KG2Book_9_Date,
    eventCelebration_April_JuneDate,
    eventCelebration_JulyDate,
    eventCelebration_AugustDate,
    eventCelebration_SeptemberDate,
    eventCelebration_OctoberDate,
    eventCelebration_NovemberDate,
    eventCelebration_DecemberDate,
    eventCelebration_JanuaryDate,
    eventCelebration_FebruaryDate,
    eventCelebration_MarchDate,
    Workshop_1Date,
    Workshop_2Date,
    Workshop_3Date,
    Workshop_4Date,
    Workshop_5Date,
    Workshop_6Date,
    Workshop_7Date,
    others_Settlers_program_Date,
    others_Circle_time_Date,
    others_Interview_Date,
    others_Academic_Overview_Date,
    others_Teacher_interaction_Date,
    others_Assessment_Date,
    others_PTM_Date,
    others_Summer_camp_Date,
    others_Winter_Camp_Date,
    others_Aayam_Date,
    others_Sports_day_Date,others_midterm_session,others_bridgecourse');
            $this->db->from('tbl_branches');
            $this->db->where('franchiseNumber', $franchiseNumber);
            $this->db->where('isDeleted', 0);
            $query = $this->db->get();

            return $query->row();
        }
        /**
         * @return array $result : This is franchise number list
         */
        function getBranchesFranchiseNumber()
        {
            $this->db->select('branchesId, franchiseNumber');
            $this->db->from('tbl_branches');
            $this->db->where('isDeleted', 0);
            $this->db->where('franchiseNumber !=', '');
            $query = $this->db->get();
            return $query->result();
        }
    /**
     * This function is used to update the branches information
     * @param array $branchesInfo : This is branches updated information
     * @param number $branchesId : This is branches id
     */
   

public function editBranches($branchesInfo, $branchesId)
{ 
    // Get the current branch data before updating
    $this->db->select('currentStatus');
    $this->db->from('tbl_branches');
    $this->db->where('branchesId', $branchesId);
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        $currentStatus = $query->row()->currentStatus;
        $franchiseNumber = 'EEIPL' . $branchesId;

        // Start a transaction to ensure atomicity
        $this->db->trans_start();

        // Check if status is being updated
        if (isset($branchesInfo['currentStatus']) && $branchesInfo['currentStatus'] != $currentStatus) {
            
            // If status changes to "UnInstalled-Active" or "UnInstalled-Closed", remove from tbl_amc
            if (in_array($branchesInfo['currentStatus'], ['UnInstalled-Active', 'UnInstalled-Closed'])) {
                $this->db->where('franchiseNumber', $franchiseNumber);
                $this->db->delete('tbl_amc');
            }

            // If status changes to "Installed-Active" or "Installed-Closed", check if the record exists
            if (in_array($branchesInfo['currentStatus'], ['Installed-Active', 'Installed-Closed'])) {
                $this->db->select('franchiseNumber');
                $this->db->from('tbl_amc');
                $this->db->where('franchiseNumber', $franchiseNumber);
                $amcQuery = $this->db->get();

               /* $amcData = array(
                    'franchiseNumber' => $franchiseNumber,
                    'franchiseName' => $branchesInfo['franchiseName'] ?? '',
                    'branchState' => $branchesInfo['branchState'] ?? '',
                    'branchcityName' => $branchesInfo['branchcityName'] ?? '',
                    'brspFranchiseAssigned' => $branchesInfo['branchFranchiseAssigned'] ?? 'Not Assigned',
                    //'branchFranchiseAssigned' => $branchesInfo['branchFranchiseAssigned'] ?? 'Not Assigned',
                    'currentStatus' => $branchesInfo['currentStatus'] ?? '',
                );*/
$brspFranchiseAssigned = !empty($branchesInfo['branchFranchiseAssigned']) 
    ? $branchesInfo['branchFranchiseAssigned'] 
    : $this->session->userdata('userId');  // fallback to session user ID

$amcData = array(
    'franchiseNumber'       => $franchiseNumber,
    'franchiseName'         => $branchesInfo['franchiseName'] ?? '',
    'branchState'           => $branchesInfo['branchState'] ?? '',
    'branchcityName'        => $branchesInfo['branchcityName'] ?? '',
    'brspFranchiseAssigned' => $brspFranchiseAssigned,
    'currentStatus'         => $branchesInfo['currentStatus'] ?? '',
);
                if ($amcQuery->num_rows() > 0) {
                    // If exists, update the record in tbl_amc
                    $this->db->where('franchiseNumber', $franchiseNumber);
                    $this->db->update('tbl_amc', $amcData);
                } else {
                    // If not exists, insert into tbl_amc (for new branches)
                    $this->db->insert('tbl_amc', $amcData);
                }
              // print_r($amcData);exit;

                // ✅ INSERT INTO tbl_brcreddetails if not already exists
                if ($branchesInfo['currentStatus'] == 'Installed-Active') {
                    $this->db->from('tbl_brcreddetails');
                    $this->db->where('franchiseNumber', $franchiseNumber);
                    $credQuery = $this->db->get();

                    if ($credQuery->num_rows() == 0) {
                        $credInfo = [
                            'franchiseNumber' => $franchiseNumber,
                            'franchiseName' => $branchesInfo['franchiseName'] ?? '',
                            // 'branchState' => $branchesInfo['branchState'] ?? '',
                    //'branchcityName' => $branchesInfo['branchcityName'] ?? '',
                    'brspFranchiseAssigned' => $branchesInfo['brspFranchiseAssigned'] ?? 'Not Assigned',
                    //'currentStatus' => $branchesInfo['currentStatus'] ?? '',
                          
                            'createdBy' => $branchesInfo['updatedBy'] ?? 0,
                            
                        ];

                        $this->db->insert('tbl_brcreddetails', $credInfo);
                    }

                    // ✅ INSERT INTO tbl_socialmedia if not already exists
                    $this->db->from('tbl_social_media');
                    $this->db->where('franchiseNumber', $franchiseNumber);
                    $socialQuery = $this->db->get();

                    if ($socialQuery->num_rows() == 0) {
                        $socialData = [
                            'franchiseNumber' => $franchiseNumber,
                            'franchiseName' => $branchesInfo['franchiseName'] ?? '',
                           'branchState' => $branchesInfo['branchState'] ?? '',
                            'branchcityName' => $branchesInfo['branchcityName'] ?? '',
                            'brspFranchiseAssigned' => $branchesInfo['brspFranchiseAssigned'] ?? 'Not Assigned',
                            'currentStatus' => $branchesInfo['currentStatus'] ?? '',
                            'createdBy' => $branchesInfo['updatedBy'] ?? 0
                    
                        ];

                        $this->db->insert('tbl_social_media', $socialData);
                    }
                }
            }
        }

        // Update tbl_branches
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_branches', $branchesInfo);

        // Complete transaction
        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    return FALSE; // No branch found
}



/*public function editBranches($branchesInfo, $branchesId)
{
    // Get the current branch data before updating
    $this->db->select('currentStatus');
    $this->db->from('tbl_branches');
    $this->db->where('branchesId', $branchesId);
    $query = $this->db->get();
    
    if ($query->num_rows() > 0) {
        // Get the current status of the branch
        $currentStatus = $query->row()->currentStatus;
        
        // Start a transaction to ensure atomicity
        $this->db->trans_start();

        // Check if the currentStatus is being updated and if it's 1 or 2
        if (isset($branchesInfo['currentStatus']) && $branchesInfo['currentStatus'] != $currentStatus) {
            // Check if the new currentStatus is 1 or 2
            if ($branchesInfo['currentStatus'] == 'Installed-Active' || $branchesInfo['currentStatus'] == 'Installed-Closed') {
                // currentStatus is being updated and is either 1 or 2, so insert into tbl_amc
                
                // Prepare AMC data - Check if the necessary keys exist in $branchesInfo
                $amcInfo = array(
                    'franchiseNumber' => 'EEIPL' . $branchesId,  // Generate franchise number using branchesId
                    'franchiseName' => isset($branchesInfo['franchiseName']) ? $branchesInfo['franchiseName'] : '',  // Default value if missing
                    'branchAddress' => isset($branchesInfo['branchAddress']) ? $branchesInfo['branchAddress'] : '',  // Default value if missing
                    'branchState' => isset($branchesInfo['branchState']) ? $branchesInfo['branchState'] : '',  // Default value if missing
                    'branchFranchiseAssigned' => isset($branchesInfo['branchFranchiseAssigned']) ? $branchesInfo['branchFranchiseAssigned'] : 'Not Assigned',  // Default value if missing
                );

                // Insert into tbl_amc
                $this->db->insert('tbl_amc', $amcInfo);
            }
        }

        // Proceed with the update of the branch data
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_branches', $branchesInfo);

        // Complete the transaction
        $this->db->trans_complete();

        // Check if the transaction was successful
        if ($this->db->trans_status() === FALSE) {
            return FALSE;
        }

        return TRUE;
    }

    return FALSE; // If no record is found with the provided branchesId
}
*/

     /**
     * This function is used to get the user  information
     * @return array $result : This is result of the query
     */
    function getUser()
    {
        /*---Growth-Support--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        // $this->db->where('userTbl.roleId', 15);
         $this->db->where_in('userTbl.roleId', [13, 15]);
        $query = $this->db->get();
        return $query->result();
    }
  function getAllUserRole()
{
    /*---Growth-Support--*/
    $this->db->select('userTbl.userId, userTbl.name');
    $this->db->from('tbl_users as userTbl');
    $this->db->where('userTbl.roleId !=', 25); // Exclude roleId = 25
    $query = $this->db->get();
    return $query->result();
}

    function getLDUser()
    {
        /*---Legal--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 24);
        $query = $this->db->get();
        return $query->result();
    }
     function getDUser()
    {
        /*---Designing--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 19);
        $query = $this->db->get();
        return $query->result();
    }
    function getADMuser()
    {
        /*---Admission--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 20);
        $query = $this->db->get();
        return $query->result();
    }
    function getDISuser()
    {
        /*---Dispatch--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 23);
        $query = $this->db->get();
        return $query->result();
    }
    function getATMuser()
    {
        /*---AdmintrainingDepartment--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 30);
        $query = $this->db->get();
        return $query->result();
    }
    function getMATuser()
    {
        /*---Material--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 17);
        $query = $this->db->get();
        return $query->result();
    }
    function getDMuser()
    {
        /*---Digital-Marketing--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 18);
        $query = $this->db->get();
        return $query->result();
    }
    function getACuser()
    {
        /*---Accounts--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 16);
        $query = $this->db->get();
        return $query->result();
    }
    function getTRMUser()
    {
        /*---Accounts--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 21);
        $query = $this->db->get();
        return $query->result();
    }
    function getSMDuser()
    {
        /*---Social-Media-Department--*/
        $this->db->select('userTbl.userId, userTbl.name');
        $this->db->from('tbl_users as userTbl');
        $this->db->where_not_in('userTbl.roleId', [1,14,2]);
        $this->db->where('userTbl.roleId', 33);
        $query = $this->db->get();
        return $query->result();
    }

        function getallLegalDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQuery($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocuments');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocuments($accessMatrix);

            $result = $this->getRoleLegalDocQuery($branchesId);
        }

        return $result;
    }

    // public function selectData($table, $where = array())
    // {
    //     $builder = $this->db->table($table);
    //     $builer-select("*");
    //     $builder->where($where);
    //     $query = $builder->get();
    //     echo $this->db->getLastQuery();
    //     // "SELECT * From `states` where id = ?"

    // }

    private function getRoleLegalDocQuery($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocuments($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents', $accessMatrix);
        $this->db->trans_complete();
    }

    function getallLegalPvtlDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQueryPVTL($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocumentsPvtLtd');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocumentsPVTL($accessMatrix);

            $result = $this->getRoleLegalDocQueryPVTL($branchesId);
        }

        return $result;
    }

    private function getRoleLegalDocQueryPVTL($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents_pvtltd');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocumentsPVTL($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents_pvtltd', $accessMatrix);
        $this->db->trans_complete();
    }

    function getallLegalSocietyDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQuerySociety($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocumentsSociety');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocumentsSociety($accessMatrix);

            $result = $this->getRoleLegalDocQuerySociety($branchesId);
        }

        return $result;
    }

    private function getRoleLegalDocQuerySociety($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents_society');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocumentsSociety($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents_society', $accessMatrix);
        $this->db->trans_complete();
    }

    function getallLegalTrustDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQueryTruest($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocumentsTrust');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocumentsTrust($accessMatrix);

            $result = $this->getRoleLegalDocQueryTruest($branchesId);
        }

        return $result;
    }

    private function getRoleLegalDocQueryTruest($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents_trust');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocumentsTrust($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents_trust', $accessMatrix);
        $this->db->trans_complete();
    }

    function getallLegalprDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQueryPR($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocumentsPartnership');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocumentsPR($accessMatrix);

            $result = $this->getRoleLegalDocQueryPR($branchesId);
        }

        return $result;
    }

    private function getRoleLegalDocQueryPR($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents_partnership');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocumentsPR($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents_partnership', $accessMatrix);
        $this->db->trans_complete();
    }

    function getallLegalHUFDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQueryHUF($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocumentsHUF');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocumentsHUF($accessMatrix);

            $result = $this->getRoleLegalDocQueryHUF($branchesId);
        }

        return $result;
    }

    private function getRoleLegalDocQueryHUF($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents_huf');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocumentsHUF($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents_huf', $accessMatrix);
        $this->db->trans_complete();
    }

    function getallLegalPropriDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQueryPropri($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocumentsProprietorship');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocumentsPropri($accessMatrix);

            $result = $this->getRoleLegalDocQueryPropri($branchesId);
        }

        return $result;
    }

    private function getRoleLegalDocQueryPropri($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents_partnership');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocumentsPropri($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents_partnership', $accessMatrix);
        $this->db->trans_complete();
    }

    function getallLegalIndividualDocument($branchesId)
    {
        /*---Get all Legal Documets--*/
        $result = $this->getRoleLegalDocQueryIndividual($branchesId);
        
        if(is_null($result)) {

            $CI = &get_instance();
            $modules = $CI->config->item('legalDocumentsIndividual');

            $accessMatrix = array('branchesId'=> $branchesId, 'access'=>json_encode($modules), 'createdBy'=> 1, 'createdDtm'=>date('Y-m-d H:i:s'));

            $this->insertLegalDocumentsIndividual($accessMatrix);

            $result = $this->getRoleLegalDocQueryIndividual($branchesId);
        }

        return $result;
    }

    private function getRoleLegalDocQueryIndividual($branchesId)
    {
        /*---Get all Legal Documets--*/
        $this->db->select('branchesId, access');
        $this->db->from('tbl_legal_documents_individual');
        $this->db->where('branchesId', $branchesId);
        $query = $this->db->get();
        
        $result = $query->row();
        return $result;
    }

    function insertLegalDocumentsIndividual($accessMatrix)
    {
        /*---Insert Legal Documets--*/
        $this->db->trans_start();
        $this->db->insert('tbl_legal_documents_individual', $accessMatrix);
        $this->db->trans_complete();
    }

    public function updateLegalDocumentsIndividual($branchesId, $accessMatrix)
{
    // Start transaction
    $this->db->trans_start();
    
    // Update the legal documents data where branchesId matches
    $this->db->where('branchesId', $branchesId);
    $this->db->update('tbl_legal_documents_individual', $accessMatrix);
    
    // Complete transaction
    $this->db->trans_complete();

    // Check the transaction status and return the result
    return $this->db->trans_status();
}


    function updateLegalDocuments($branchesId, $accessMatrix)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents', $accessMatrix);

        return $this->db->affected_rows();
    }

    function updateLegalPVTLDocuments($branchesId, $accessMatrix1)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents_pvtltd', $accessMatrix1);

        return $this->db->affected_rows();
    }
    function updateLegalSocietyDocuments($branchesId, $accessMatrix5)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents_society', $accessMatrix5);

        return $this->db->affected_rows();
    }

    function updateLegalTurstDocuments($branchesId, $accessMatrix4)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents_trust', $accessMatrix4);

        return $this->db->affected_rows();
    }

    function updateLegalPRTDocuments($branchesId, $accessMatrix3)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents_partnership', $accessMatrix3);

        return $this->db->affected_rows();
    }

    function updateLegalHUFDocuments($branchesId, $accessMatrix2)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents_huf', $accessMatrix2);

        return $this->db->affected_rows();
    }

   public function updateLegalPropriDocuments($branchesId, $accessMatrix1)
{
    $this->db->where('branchesId', $branchesId);
    $this->db->update('tbl_legal_documents_proprietorship', $accessMatrix1);

    return $this->db->affected_rows();
}

/*public function updateLegalPropriDocuments($branchesId, $accessMatrix1)
{
    // Start transaction
    $this->db->trans_start();
    
    // Update the legal documents data where branchesId matches
    $this->db->where('branchesId', $branchesId);
    $this->db->update('tbl_legal_documents_proprietorship', $accessMatrix1);
    
    // Complete transaction
    $this->db->trans_complete();

    // Check the transaction status and return the result
    return $this->db->trans_status();
}*/


    function updateLegalInvdDocuments($branchesId, $accessMatrix1)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents_individual', $accessMatrix1);

        return $this->db->affected_rows();
    }
    /*** This function is used to import csv file */
    /*public function insertCsvFile($data) {
        $this->db->insert('tbl_branches', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }*/
    /*** This function is used to import csv file */
    public function insertCsvFile($data) {
        $this->db->insert('tbl_branches', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
	
	// code done by yashi 
	/*public function get_count($franchiseFilter = null, $role = null, $userId = null) {
     if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }
	if (!in_array($role, [1, 2, 14,19,16,17,18,20,21,22,23,24,34,30]) && $userId) {
        $conditions = "(branchFranchiseAssigned = ".$userId." 
                      )";

        $this->db->where($conditions);
    }
	return $this->db->count_all_results('tbl_branches');
}*/
public function get_count($franchiseFilter = null, $role = null, $userId = null, $currentStatus = null, $searchText = null, $growthManagerFilter = null) {
    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }

    if ($currentStatus) {
        $this->db->where('currentStatus', $currentStatus);
    }

    if ($growthManagerFilter) {
        $this->db->where('branchFranchiseAssigned', $growthManagerFilter);
    }

    if ($searchText) {
        $this->db->group_start();
        $this->db->like('applicantName', $searchText);
        $this->db->group_end();
    }

    $allowedRoles = [1, 2, 14, 19, 16, 17, 18, 20, 21, 22, 23, 24, 34, 30, 33];
    if (!in_array($role, $allowedRoles) && $userId) {
        $this->db->where('branchFranchiseAssigned', $userId);
    }

    return $this->db->count_all_results('tbl_branches');
}



	public function getFranchiseNumberByUserId($userId) {
        $this->db->select('franchiseNumber');
        $this->db->from('tbl_users');
        $this->db->where('userId', $userId);
        $query = $this->db->get();

        $result = $query->row();
        return $result ? $result->franchiseNumber : null;
    }
public function get_count_by_franchise($franchiseNumber, $franchiseFilter = null, $currentStatus = null, $searchText = null, $growthManagerFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);

    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }

    if ($currentStatus) {
        $this->db->where('currentStatus', $currentStatus);
    }

    if ($growthManagerFilter) {
        $this->db->where('branchFranchiseAssigned', $growthManagerFilter);
    }

    if ($searchText) {
        $this->db->group_start();
        $this->db->like('applicantName', $searchText);
        $this->db->group_end();
    }

    return $this->db->count_all_results('tbl_branches');
}


	public function get_data($limit, $start, $franchiseFilter = null, $role = null, $userId = null, $currentStatus = null, $searchText = null, $growthManagerFilter = null) {
    $this->db->limit($limit, $start);

    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }

    if ($currentStatus) {
        $this->db->where('currentStatus', $currentStatus);
    }

    if ($growthManagerFilter) {
        $this->db->where('branchFranchiseAssigned', $growthManagerFilter);
    }

    if ($searchText) {
        $this->db->group_start();
        $this->db->like('applicantName', $searchText);
        $this->db->group_end();
    }

    $allowedRoles = [1, 2, 14, 19, 16, 17, 18, 20, 21, 22, 23, 24, 34, 30, 33];
    if (!in_array($role, $allowedRoles) && $userId) {
        $this->db->where('branchFranchiseAssigned', $userId);
    }

    $query = $this->db->get('tbl_branches');
    return $query->result();
}



	
   public function get_data_by_franchise($franchiseNumber, $limit, $start, $franchiseFilter = null, $currentStatus = null, $searchText = null, $growthManagerFilter = null) {
    $this->db->where('franchiseNumber', $franchiseNumber);
    $this->db->limit($limit, $start);

    if ($franchiseFilter) {
        $this->db->where('franchiseNumber', $franchiseFilter);
    }

    if ($currentStatus) {
        $this->db->where('currentStatus', $currentStatus);
    }

    if ($growthManagerFilter) {
        $this->db->where('branchFranchiseAssigned', $growthManagerFilter);
    }

    if ($searchText) {
        $this->db->group_start();
        $this->db->like('applicantName', $searchText);
        $this->db->group_end();
    }

    $query = $this->db->get('tbl_branches');
    return $query->result();
}

public function getUserByFranchiseNumber($franchiseNumber)
{
    $this->db->select('userId');
    $this->db->from('tbl_users'); // ✅ Ensure the correct table name
    $this->db->where('franchiseNumber', $franchiseNumber);
    $query = $this->db->get();

    return $query->row(); // Return single row (userId)
}
public function getUsersByBranch($branchId)
{
    $this->db->select('userId');
    $this->db->from('tbl_users');  // Ensure this is the correct table
    $this->db->where('franchiseNumber', $branchId);

    $query = $this->db->get();
    return $query->result_array();  // Returns an array of assigned users
}
public function getUsersByRoles($roles)
{
    $this->db->select('userId');
    $this->db->from('tbl_users');
    $this->db->where_in('roleId', $roles);
    $query = $this->db->get();
    return $query->result();
}
public function getGrowthManagers() {
    $this->db->select('userId, name');
    $this->db->from('tbl_users');
    $this->db->where('roleId', 15); // assuming roleId 16 is Growth Manager
    $this->db->order_by('name', 'ASC');
    return $this->db->get()->result();
}
}