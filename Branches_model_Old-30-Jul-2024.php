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
        $this->db->select('BaseTbl.branchesId, BaseTbl.applicantName, BaseTbl.branchEmail, BaseTbl.mobile, BaseTbl.branchcityName, BaseTbl.branchState, BaseTbl.branchSalesDoneby, BaseTbl.branchAmountReceived, BaseTbl.branchFranchiseAssigned, BaseTbl.branchAddress, BaseTbl.permanentAddress,  BaseTbl.franchiseNumber, BaseTbl.franchiseName, BaseTbl.typeBranch, BaseTbl.currentStatus, BaseTbl.bookingDate, BaseTbl.licenseNumber, BaseTbl.licenseSharedon, BaseTbl.validFromDate, BaseTbl.validTillDate, BaseTbl.branchLocation, BaseTbl.adminName, BaseTbl.adminContactNum, BaseTbl.additionalNumber, BaseTbl.officialEmailID, BaseTbl.personalEmailId, BaseTbl.biometricInstalled, BaseTbl.biometricRemark, BaseTbl.biometricInstalledDate, BaseTbl.camaraInstalled, BaseTbl.camaraRemark, BaseTbl.camaraInstalledDate, BaseTbl.eduMetaAppTraining, BaseTbl.AppTrainingRemark, BaseTbl.AppTrainingRemarkDate, BaseTbl.congratulationsImg, BaseTbl.brimguploadedFBStatus, BaseTbl.brimguploadedFBDate, BaseTbl.brimguploadedInstaStatus, BaseTbl.brimguploadedInstaDate, BaseTbl.admissionOpenimgStatus, BaseTbl.staffHiringimgStatus, BaseTbl.newsletterMarch, BaseTbl.newsletterJune, BaseTbl.newsletterSeptember, BaseTbl.newsletterDecember, BaseTbl.OBirthDayImgStatus, BaseTbl.OBirthDayImgSharedDtm, BaseTbl.OwnerAnnImgStatus, BaseTbl.OwnerAnnImgSharedDtm, BaseTbl.facebookPageStatus, BaseTbl.facebookPageLink, BaseTbl.facebookPageRemark, BaseTbl.googleMapLoc, BaseTbl.googleMapLocLink, BaseTbl.googleMapLocRemark, BaseTbl.instagramPageStatus, BaseTbl.instagramPageID, BaseTbl.instagramPageRemark, BaseTbl.jdPageStatus, BaseTbl.jdPageID, BaseTbl.jdPageRemark, BaseTbl.tweetPageStatus, BaseTbl.tweetPageID, BaseTbl.tweetPageRemark, BaseTbl.digiMarkCost, BaseTbl.digiMarkStartDtm, BaseTbl.digiMarkEndDtm, BaseTbl.digiMarkReamrk, BaseTbl.insfeedvideoUplodFB, BaseTbl.insfeedvideoUplodYoutube, BaseTbl.insfeedvideoUplodInsta, BaseTbl.branchLocAddressPremise, BaseTbl.addOfFranchise, BaseTbl.gstNumber, BaseTbl.undertakingCommitmentSupport, BaseTbl.amcAmount, BaseTbl.invoiceNumber, BaseTbl.agreementTenure, BaseTbl.salesExecutive, BaseTbl.salesTeamlead, BaseTbl.Manual1, BaseTbl.Manual2, BaseTbl.Manual3, BaseTbl.Reference, BaseTbl.installationTentativeDate, BaseTbl.formsDocumentsCompleted, BaseTbl.setUpInstallation, BaseTbl.branchAnniversaryDate, BaseTbl.admissionCracked, BaseTbl.teacherRecruitment, BaseTbl.pgDecidedFee, BaseTbl.nurseryDecidedFee, BaseTbl.KG1DecidedFee, BaseTbl.KG2DecidedFee, BaseTbl.feeSharedStatus, BaseTbl.feesRemark, BaseTbl.addmissionPG, BaseTbl.addmissionNursary, BaseTbl.addmissionKg1, BaseTbl.addmissionKg2, BaseTbl.addmission1st, BaseTbl.addmission2nd, BaseTbl.totalAddmission, BaseTbl.addmissionCounselor, BaseTbl.lastDiscussaddmission, BaseTbl.addmissionSheetlink, BaseTbl.dateexlSheetshared, BaseTbl.lastInteractiondate, BaseTbl.lastDiscussionby, BaseTbl.lastInteractioncomment, BaseTbl.agreementDraftdate, BaseTbl.branchLandline, BaseTbl.additionalName, BaseTbl.finalPaydeadline, BaseTbl.BranchSpecialNoteSales, BaseTbl.completeFranchiseAmt, BaseTbl.confirmationAmt33kGST, BaseTbl.happinessLevelbranch, BaseTbl.DesignsPromotional, BaseTbl.DesignsPromotionalRemark, BaseTbl.BranchSpecialNote, BaseTbl.OwnerAnniversery, BaseTbl.welcomeCall, BaseTbl.welcomeMail, BaseTbl.whatsappGroup, BaseTbl.whatsappGroupRemark, BaseTbl.whatsappGroupdate, BaseTbl.interactionMeeting, BaseTbl.interactionMeetingRemark, BaseTbl.undertakingCommitment, BaseTbl.onboardingForm, BaseTbl.onboardingFormReceived, BaseTbl.onboardingFormRemark, BaseTbl.installationRequirementmail, BaseTbl.installationRequirementmailRemark, BaseTbl.agreementDraftReceiveddate, BaseTbl.compFileSubmit, BaseTbl.fileCLoserDate, BaseTbl.branchStatusRemark, BaseTbl.finalAgreementShared, BaseTbl.officialemailshared, BaseTbl.adminTraining, BaseTbl.inaugurationDate, BaseTbl.classroomDecoration, BaseTbl.movieClub, BaseTbl.referEarn, BaseTbl.teacherInteraction, BaseTbl.teacherInterview, BaseTbl.pongalWorkshop, BaseTbl.sankrantiWorkshop, BaseTbl.republicDayWorkshop, BaseTbl.bridgeCourseCounselling, BaseTbl.bulletinBoard, BaseTbl.bridgeCourse, BaseTbl.settlersProgram, BaseTbl.jollyPhonic, BaseTbl.academicsMeetings, BaseTbl.timeDisclipineemail, BaseTbl.uniformDisclipineemail, BaseTbl.curiculumnShared, BaseTbl.holidaEventlisting, BaseTbl.sharingAssessmentpapers, BaseTbl.assessmentSharingemail, BaseTbl.PTMscheduledate, BaseTbl.shadowPuppet, BaseTbl.monthlyEventtraining, BaseTbl.summertCampdate, BaseTbl.winterCampdate, BaseTbl.offerName, BaseTbl.offerPlanname, BaseTbl.discountAmount, BaseTbl.finalAmount, BaseTbl.legalChargesSales, BaseTbl.brSetupinsChargSales, BaseTbl.numInialKitSales, BaseTbl.franchiseTenure, BaseTbl.welComeFolderStatus, BaseTbl.welComeFolderDtm, BaseTbl.trainingAmount, BaseTbl.societyServiceamount, BaseTbl.totalAmount, BaseTbl.gstAmount, BaseTbl.totalfranchisegstFund, BaseTbl.legalCharges, BaseTbl.legalChargesdue, BaseTbl.totalgstCharges, BaseTbl.totalPaidamount, BaseTbl.dueFranchiseamt, BaseTbl.kitCharges, BaseTbl.numinitialKit, BaseTbl.totalKitsamt, BaseTbl.kitamtReceived, BaseTbl.dueKitamount, BaseTbl.installationDate, BaseTbl.finaltotalamtDue, BaseTbl.specialRemark, BaseTbl.transporttravCharge, BaseTbl.brsetupinstachargReceived, BaseTbl.brsetupinstachargDue, BaseTbl.travelAmount, BaseTbl.receivedtravelAmount, BaseTbl.duetravelAmount, BaseTbl.transportCharges, BaseTbl.transportAmtreceived, BaseTbl.duetransportCharges, BaseTbl.ledgerMarch, BaseTbl.ledgerJune, BaseTbl.ledgerSeptember, BaseTbl.ledgerDecember, BaseTbl.reminderAMCStatus1Dec, BaseTbl.reminderAMCStatus10Dec, BaseTbl.reminderAMCStatus15Dec, BaseTbl.reminderAMCStatus19Dec, BaseTbl.reminderAMCStatus20Dec, BaseTbl.RemarkforAMCmail, BaseTbl.InvoiceAMCClearance, BaseTbl.PenaltyMailnoncle, BaseTbl.invoiceNumberAll, BaseTbl.upgradeUptoclass, BaseTbl.branchStatus, BaseTbl.brInstallationStatus, BaseTbl.undertakingAck, BaseTbl.optOnlineMarketing, BaseTbl.insmatDispatchdate, BaseTbl.DetailsReceiptmail, BaseTbl.ConfBrinsScheduledemail, BaseTbl.Materialrecdate, BaseTbl.BrinsScheduleddate, BaseTbl.BrinsScheduledemail, BaseTbl.brInstalationRemark, BaseTbl.videoFeedbackbr, BaseTbl.writtenFeedbackbr, BaseTbl.ShoppinPortSharedDate, BaseTbl.ShoppinPortTraining, BaseTbl.ShoppinPortTrainingDate, BaseTbl.ShoppinPortRemark, BaseTbl.returnItems, BaseTbl.modeOfDespatch, BaseTbl.NumOfBoxes, BaseTbl.PoDNum, BaseTbl.SpecificGiftOffer, BaseTbl.ConfBrInsOverPhone, BaseTbl.shortComming, BaseTbl.solutionShortComming, BaseTbl.customWebsiteLink, BaseTbl.createdDtm');
        $this->db->from('tbl_branches as BaseTbl');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.applicantName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        if (!in_array($role, [1,2,14])) {
            $conditions = "(branchFranchiseAssigned=".$UserID." OR branchFranchiseAssignedDesigning=".$UserID." OR branchFranchiseAssignedLegalDepartment=".$UserID." OR branchFrAssignedAccountsDepartment=".$UserID." OR branchFrAssignedDispatchDepartment=".$UserID." OR branchFrAssignedAdmintrainingDepartment=".$UserID." OR branchFrAssignedAdmissionDepartment=".$UserID." OR branchFrAssignedMaterialDepartment=".$UserID." OR branchFrAssignedDigitalDepartment=".$UserID." OR  branchFrAssignedTrainingDepartment=".$UserID.")";

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
        $this->db->select('BaseTbl.branchesId, BaseTbl.applicantName, BaseTbl.branchEmail, BaseTbl.mobile, BaseTbl.branchcityName, BaseTbl.branchState, BaseTbl.branchSalesDoneby, BaseTbl.branchAmountReceived, userTbl.name as branchFranchiseAssigned, BaseTbl.branchAddress, BaseTbl.permanentAddress,  BaseTbl.franchiseNumber, BaseTbl.franchiseName, BaseTbl.typeBranch, BaseTbl.currentStatus, BaseTbl.bookingDate, BaseTbl.licenseNumber, BaseTbl.licenseSharedon, BaseTbl.validFromDate, BaseTbl.validTillDate, BaseTbl.branchLocation, BaseTbl.adminName, BaseTbl.adminContactNum, BaseTbl.additionalNumber, BaseTbl.officialEmailID, BaseTbl.personalEmailId, BaseTbl.biometricInstalled, BaseTbl.biometricRemark, BaseTbl.biometricInstalledDate, BaseTbl.camaraInstalled, BaseTbl.camaraRemark, BaseTbl.camaraInstalledDate, BaseTbl.eduMetaAppTraining, BaseTbl.AppTrainingRemark, BaseTbl.AppTrainingRemarkDate, BaseTbl.congratulationsImg, BaseTbl.brimguploadedFBStatus, BaseTbl.brimguploadedFBDate, BaseTbl.brimguploadedInstaStatus, BaseTbl.brimguploadedInstaDate, BaseTbl.admissionOpenimgStatus, BaseTbl.staffHiringimgStatus, BaseTbl.newsletterMarch, BaseTbl.newsletterJune, BaseTbl.newsletterSeptember, BaseTbl.newsletterDecember, BaseTbl.OBirthDayImgStatus, BaseTbl.OBirthDayImgSharedDtm, BaseTbl.OwnerAnnImgStatus, BaseTbl.OwnerAnnImgSharedDtm, BaseTbl.facebookPageStatus, BaseTbl.facebookPageLink, BaseTbl.facebookPageRemark, BaseTbl.googleMapLoc, BaseTbl.googleMapLocLink, BaseTbl.googleMapLocRemark, BaseTbl.instagramPageStatus, BaseTbl.instagramPageID, BaseTbl.instagramPageRemark, BaseTbl.jdPageStatus, BaseTbl.jdPageID, BaseTbl.jdPageRemark, BaseTbl.tweetPageStatus, BaseTbl.tweetPageID, BaseTbl.tweetPageRemark, BaseTbl.digiMarkCost, BaseTbl.digiMarkStartDtm, BaseTbl.digiMarkEndDtm, BaseTbl.digiMarkReamrk, BaseTbl.insfeedvideoUplodFB, BaseTbl.insfeedvideoUplodYoutube, BaseTbl.insfeedvideoUplodInsta, BaseTbl.branchLocAddressPremise, BaseTbl.addOfFranchise, BaseTbl.gstNumber, BaseTbl.undertakingCommitmentSupport, BaseTbl.amcAmount, BaseTbl.invoiceNumber, BaseTbl.agreementTenure, BaseTbl.salesExecutive, BaseTbl.salesTeamlead, BaseTbl.Manual1, BaseTbl.Manual2, BaseTbl.Manual3, BaseTbl.Reference, BaseTbl.installationTentativeDate, BaseTbl.formsDocumentsCompleted, BaseTbl.setUpInstallation, BaseTbl.branchAnniversaryDate, BaseTbl.admissionCracked, BaseTbl.teacherRecruitment, BaseTbl.pgDecidedFee, BaseTbl.nurseryDecidedFee, BaseTbl.KG1DecidedFee, BaseTbl.KG2DecidedFee, BaseTbl.feeSharedStatus, BaseTbl.feesRemark, BaseTbl.addmissionPG, BaseTbl.addmissionNursary, BaseTbl.addmissionKg1, BaseTbl.addmissionKg2, BaseTbl.addmission1st, BaseTbl.addmission2nd, BaseTbl.totalAddmission, BaseTbl.addmissionCounselor, BaseTbl.lastDiscussaddmission, BaseTbl.addmissionSheetlink, BaseTbl.dateexlSheetshared, BaseTbl.lastInteractiondate, BaseTbl.lastDiscussionby, BaseTbl.lastInteractioncomment, BaseTbl.agreementDraftdate, BaseTbl.branchLandline, BaseTbl.additionalName, BaseTbl.finalPaydeadline, BaseTbl.BranchSpecialNoteSales, BaseTbl.completeFranchiseAmt, BaseTbl.confirmationAmt33kGST, BaseTbl.happinessLevelbranch, BaseTbl.DesignsPromotional, BaseTbl.DesignsPromotionalRemark, BaseTbl.BranchSpecialNote, BaseTbl.OwnerAnniversery, BaseTbl.welcomeCall, BaseTbl.welcomeMail, BaseTbl.whatsappGroup, BaseTbl.whatsappGroupRemark, BaseTbl.whatsappGroupdate, BaseTbl.interactionMeeting, BaseTbl.interactionMeetingRemark, BaseTbl.undertakingCommitment, BaseTbl.onboardingForm, BaseTbl.onboardingFormReceived, BaseTbl.onboardingFormRemark, BaseTbl.installationRequirementmail, BaseTbl.installationRequirementmailRemark, BaseTbl.agreementDraftReceiveddate, BaseTbl.compFileSubmit, BaseTbl.fileCLoserDate, BaseTbl.branchStatusRemark, BaseTbl.officialemailshared, BaseTbl.finalAgreementShared, BaseTbl.officialemailshared, BaseTbl.adminTraining, BaseTbl.inaugurationDate, BaseTbl.classroomDecoration, BaseTbl.movieClub, BaseTbl.referEarn, BaseTbl.teacherInteraction, BaseTbl.teacherInterview, BaseTbl.pongalWorkshop, BaseTbl.sankrantiWorkshop, BaseTbl.republicDayWorkshop, BaseTbl.bridgeCourseCounselling, BaseTbl.bulletinBoard, BaseTbl.bridgeCourse, BaseTbl.settlersProgram, BaseTbl.jollyPhonic, BaseTbl.academicsMeetings, BaseTbl.timeDisclipineemail, BaseTbl.uniformDisclipineemail, BaseTbl.curiculumnShared, BaseTbl.holidaEventlisting, BaseTbl.sharingAssessmentpapers, BaseTbl.assessmentSharingemail, BaseTbl.PTMscheduledate, BaseTbl.shadowPuppet, BaseTbl.monthlyEventtraining, BaseTbl.summertCampdate, BaseTbl.winterCampdate, BaseTbl.offerName, BaseTbl.offerPlanname, BaseTbl.discountAmount, BaseTbl.finalAmount, BaseTbl.legalChargesSales, BaseTbl.brSetupinsChargSales, BaseTbl.numInialKitSales, BaseTbl.franchiseTenure, BaseTbl.welComeFolderStatus, BaseTbl.welComeFolderDtm, BaseTbl.trainingAmount, BaseTbl.societyServiceamount, BaseTbl.totalAmount, BaseTbl.gstAmount, BaseTbl.totalfranchisegstFund, BaseTbl.legalCharges, BaseTbl.legalChargesdue, BaseTbl.totalgstCharges, BaseTbl.totalPaidamount, BaseTbl.dueFranchiseamt, BaseTbl.kitCharges, BaseTbl.numinitialKit, BaseTbl.totalKitsamt, BaseTbl.kitamtReceived, BaseTbl.dueKitamount, BaseTbl.installationDate, BaseTbl.finaltotalamtDue, BaseTbl.specialRemark, BaseTbl.transporttravCharge, BaseTbl.brsetupinstachargReceived, BaseTbl.brsetupinstachargDue, BaseTbl.travelAmount, BaseTbl.receivedtravelAmount, BaseTbl.duetravelAmount, BaseTbl.transportCharges, BaseTbl.transportAmtreceived, BaseTbl.duetransportCharges, BaseTbl.ledgerMarch, BaseTbl.ledgerJune, BaseTbl.ledgerSeptember, BaseTbl.ledgerDecember, BaseTbl.reminderAMCStatus1Dec, BaseTbl.reminderAMCStatus10Dec, BaseTbl.reminderAMCStatus15Dec, BaseTbl.reminderAMCStatus19Dec, BaseTbl.reminderAMCStatus20Dec, BaseTbl.RemarkforAMCmail, BaseTbl.InvoiceAMCClearance, BaseTbl.PenaltyMailnoncle, BaseTbl.invoiceNumberAll, BaseTbl.upgradeUptoclass, BaseTbl.branchStatus, BaseTbl.brInstallationStatus, BaseTbl.undertakingAck, BaseTbl.optOnlineMarketing, BaseTbl.insmatDispatchdate, BaseTbl.DetailsReceiptmail, BaseTbl.ConfBrinsScheduledemail, BaseTbl.Materialrecdate, BaseTbl.BrinsScheduleddate, BaseTbl.BrinsScheduledemail, BaseTbl.brInstalationRemark, BaseTbl.videoFeedbackbr, BaseTbl.writtenFeedbackbr, BaseTbl.ShoppinPortSharedDate, BaseTbl.ShoppinPortTraining, BaseTbl.ShoppinPortTrainingDate, BaseTbl.ShoppinPortRemark, BaseTbl.returnItems, BaseTbl.modeOfDespatch, BaseTbl.NumOfBoxes, BaseTbl.PoDNum, BaseTbl.SpecificGiftOffer, BaseTbl.ConfBrInsOverPhone, BaseTbl.shortComming, BaseTbl.solutionShortComming, BaseTbl.customWebsiteLink, BaseTbl.createdDtm');
        $this->db->from('tbl_branches as BaseTbl');
        $this->db->join('tbl_users as userTbl', 'BaseTbl.branchFranchiseAssigned = userTbl.userId', 'LEFT');
        if(!empty($searchText)) {
            $likeCriteria = "(BaseTbl.applicantName LIKE '%".$searchText."%')";
            $this->db->where($likeCriteria);
        }
        if (!in_array($role, [1,2,14])) {
            $conditions = "(branchFranchiseAssigned=".$UserID." OR branchFranchiseAssignedDesigning=".$UserID." OR branchFranchiseAssignedLegalDepartment=".$UserID." OR branchFrAssignedAccountsDepartment=".$UserID." OR branchFrAssignedDispatchDepartment=".$UserID." OR branchFrAssignedAdmintrainingDepartment=".$UserID." OR branchFrAssignedAdmissionDepartment=".$UserID." OR branchFrAssignedMaterialDepartment=".$UserID." OR branchFrAssignedDigitalDepartment=".$UserID." OR branchFrAssignedTrainingDepartment=".$UserID.")";

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
    function addNewBranches($branchesInfo)
    {
        $this->db->trans_start();
        $this->db->insert('tbl_branches', $branchesInfo);
        
        $insert_id = $this->db->insert_id();
        
        $this->db->trans_complete();
        
        return $insert_id;
    }
    
    /**
     * This function used to get booking information by id
     * @param number $bookingId : This is booking id
     * @return array $result : This is booking information
     */
    function getBranchesInfo($branchesId)
    {
        $this->db->select('branchesId, applicantName, mobile, branchcityName, branchState, branchSalesDoneby, branchAmountReceived, branchFranchiseAssigned, branchFranchiseAssignedLegalDepartment, branchFranchiseAssignedDesigning, branchFrAssignedAccountsDepartment, branchFrAssignedDispatchDepartment, branchFrAssignedAdmintrainingDepartment, branchFrAssignedAdmissionDepartment, branchFrAssignedMaterialDepartment, branchFrAssignedDigitalDepartment, branchFrAssignedTrainingDepartment, branchAddress, permanentAddress, branchEmail, franchiseNumber, franchiseName, typeBranch, currentStatus, bookingDate, licenseNumber, licenseSharedon, validFromDate, validTillDate, branchLocation, adminName, adminContactNum, additionalNumber, officialEmailID, personalEmailId, biometricInstalled, biometricRemark, biometricInstalledDate, camaraInstalled, camaraRemark, camaraInstalledDate, eduMetaAppTraining, AppTrainingRemark, AppTrainingRemarkDate, congratulationsImg, brimguploadedFBStatus, brimguploadedFBDate, brimguploadedInstaStatus, brimguploadedInstaDate, admissionOpenimgStatus, staffHiringimgStatus, newsletterMarch, newsletterJune, newsletterSeptember, newsletterDecember, OBirthDayImgStatus, OBirthDayImgSharedDtm, OwnerAnnImgStatus, OwnerAnnImgSharedDtm, facebookPageStatus, facebookPageLink, facebookPageRemark, googleMapLoc, googleMapLocLink, googleMapLocRemark, instagramPageStatus, instagramPageID, instagramPageRemark, jdPageStatus, jdPageID, jdPageRemark, tweetPageStatus, tweetPageID, tweetPageRemark, digiMarkCost, digiMarkStartDtm, digiMarkEndDtm, digiMarkReamrk, insfeedvideoUplodFB, insfeedvideoUplodYoutube, insfeedvideoUplodInsta, branchLocAddressPremise, addOfFranchise, gstNumber, undertakingCommitmentSupport, amcAmount, invoiceNumber, agreementTenure, salesExecutive, salesTeamlead, Manual1, Manual2, Manual3, Reference, installationTentativeDate, formsDocumentsCompleted, setUpInstallation, branchAnniversaryDate, admissionCracked, teacherRecruitment, pgDecidedFee, nurseryDecidedFee, KG1DecidedFee, KG2DecidedFee, feeSharedStatus, feesRemark, addmissionPG, addmissionNursary, addmissionKg1, addmissionKg2, addmission1st, addmission2nd, totalAddmission, addmissionCounselor, lastDiscussaddmission, addmissionSheetlink, dateexlSheetshared, lastInteractiondate, lastDiscussionby, lastInteractioncomment, agreementDraftdate, branchLandline, additionalName, finalPaydeadline, BranchSpecialNoteSales, completeFranchiseAmt, confirmationAmt33kGST, happinessLevelbranch, DesignsPromotional, DesignsPromotionalRemark, BranchSpecialNote, OwnerAnniversery, welcomeCall, welcomeMail, whatsappGroup, whatsappGroupRemark, whatsappGroupdate, interactionMeeting, interactionMeetingRemark, undertakingCommitment, onboardingForm, onboardingFormReceived, onboardingFormRemark, installationRequirementmail, installationRequirementmailRemark,  finalAgreementShared, agreementDraftReceiveddate, compFileSubmit, fileCLoserDate, branchStatusRemark, officialemailshared, adminTraining, inaugurationDate, classroomDecoration, movieClub, referEarn, teacherInteraction, teacherInterview, pongalWorkshop, sankrantiWorkshop, republicDayWorkshop, bridgeCourseCounselling, bulletinBoard, bridgeCourse, settlersProgram, jollyPhonic, academicsMeetings, timeDisclipineemail, uniformDisclipineemail, curiculumnShared, holidaEventlisting, sharingAssessmentpapers, assessmentSharingemail, PTMscheduledate, shadowPuppet, monthlyEventtraining, summertCampdate, winterCampdate, offerName, offerPlanname, discountAmount, finalAmount, legalChargesSales, brSetupinsChargSales, numInialKitSales, franchiseTenure, welComeFolderStatus, welComeFolderDtm, trainingAmount, societyServiceamount, totalAmount, gstAmount, totalfranchisegstFund, legalCharges, legalChargesdue, totalgstCharges, totalPaidamount, dueFranchiseamt, kitCharges, numinitialKit, totalKitsamt, kitamtReceived, dueKitamount, installationDate, finaltotalamtDue, specialRemark, transporttravCharge, brsetupinstachargReceived, brsetupinstachargDue, travelAmount, receivedtravelAmount, duetravelAmount, transportCharges, transportAmtreceived, duetransportCharges, ledgerMarch, ledgerJune, ledgerSeptember, ledgerDecember, reminderAMCStatus1Dec, reminderAMCStatus10Dec, reminderAMCStatus15Dec, reminderAMCStatus19Dec, reminderAMCStatus20Dec, RemarkforAMCmail, InvoiceAMCClearance, PenaltyMailnoncle, invoiceNumberAll, upgradeUptoclass, branchStatus, brInstallationStatus, undertakingAck, optOnlineMarketing, insmatDispatchdate, DetailsReceiptmail, ConfBrinsScheduledemail, Materialrecdate, BrinsScheduleddate, BrinsScheduledemail, brInstalationRemark, videoFeedbackbr, writtenFeedbackbr, ShoppinPortSharedDate, ShoppinPortTraining, ShoppinPortTrainingDate, ShoppinPortRemark, returnItems, modeOfDespatch, NumOfBoxes, PoDNum, SpecificGiftOffer, ConfBrInsOverPhone, shortComming, solutionShortComming, customWebsiteLink,');
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
            $this->db->select('branchesId, applicantName, mobile, branchcityName, branchState, branchSalesDoneby, branchAmountReceived, branchFranchiseAssigned, branchFranchiseAssignedLegalDepartment, branchFranchiseAssignedDesigning, branchFrAssignedAccountsDepartment, branchFrAssignedDispatchDepartment, branchFrAssignedAdmintrainingDepartment, branchFrAssignedAdmissionDepartment, branchFrAssignedMaterialDepartment, branchFrAssignedDigitalDepartment, branchFrAssignedTrainingDepartment, branchAddress, permanentAddress, branchEmail, franchiseNumber, franchiseName, typeBranch, currentStatus, bookingDate, licenseNumber, licenseSharedon, validFromDate, validTillDate, branchLocation, adminName, adminContactNum, additionalNumber, officialEmailID, personalEmailId, biometricInstalled, biometricRemark, biometricInstalledDate, camaraInstalled, camaraRemark, camaraInstalledDate, eduMetaAppTraining, AppTrainingRemark, AppTrainingRemarkDate, congratulationsImg, brimguploadedFBStatus, brimguploadedFBDate, brimguploadedInstaStatus, brimguploadedInstaDate, admissionOpenimgStatus, staffHiringimgStatus, newsletterMarch, newsletterJune, newsletterSeptember, newsletterDecember, OBirthDayImgStatus, OBirthDayImgSharedDtm, OwnerAnnImgStatus, OwnerAnnImgSharedDtm, facebookPageStatus, facebookPageLink, facebookPageRemark, googleMapLoc, googleMapLocLink, googleMapLocRemark, instagramPageStatus, instagramPageID, instagramPageRemark, jdPageStatus, jdPageID, jdPageRemark, tweetPageStatus, tweetPageID, tweetPageRemark, digiMarkCost, digiMarkStartDtm, digiMarkEndDtm, digiMarkReamrk, insfeedvideoUplodFB, insfeedvideoUplodYoutube, insfeedvideoUplodInsta, branchLocAddressPremise, addOfFranchise, gstNumber, undertakingCommitmentSupport, amcAmount, invoiceNumber, agreementTenure, salesExecutive, salesTeamlead, Manual1, Manual2, Manual3, Reference, installationTentativeDate, formsDocumentsCompleted, setUpInstallation, branchAnniversaryDate, admissionCracked, teacherRecruitment, pgDecidedFee, nurseryDecidedFee, KG1DecidedFee, KG2DecidedFee, feeSharedStatus, feesRemark, addmissionPG, addmissionNursary, addmissionKg1, addmissionKg2, addmission1st, addmission2nd, totalAddmission, addmissionCounselor, lastDiscussaddmission, addmissionSheetlink, dateexlSheetshared, lastInteractiondate, lastDiscussionby, lastInteractioncomment, agreementDraftdate, branchLandline, additionalName, finalPaydeadline, BranchSpecialNoteSales, completeFranchiseAmt, confirmationAmt33kGST, happinessLevelbranch, DesignsPromotional, DesignsPromotionalRemark, BranchSpecialNote, OwnerAnniversery, welcomeCall, welcomeMail, whatsappGroup, whatsappGroupRemark, whatsappGroupdate, interactionMeeting, interactionMeetingRemark, undertakingCommitment, onboardingForm, onboardingFormReceived, onboardingFormRemark, installationRequirementmail, installationRequirementmailRemark,  finalAgreementShared, agreementDraftReceiveddate, compFileSubmit, fileCLoserDate, branchStatusRemark, officialemailshared, adminTraining, inaugurationDate, classroomDecoration, movieClub, referEarn, teacherInteraction, teacherInterview, pongalWorkshop, sankrantiWorkshop, republicDayWorkshop, bridgeCourseCounselling, bulletinBoard, bridgeCourse, settlersProgram, jollyPhonic, academicsMeetings, timeDisclipineemail, uniformDisclipineemail, curiculumnShared, holidaEventlisting, sharingAssessmentpapers, assessmentSharingemail, PTMscheduledate, shadowPuppet, monthlyEventtraining, summertCampdate, winterCampdate, offerName, offerPlanname, discountAmount, finalAmount, legalChargesSales, brSetupinsChargSales, numInialKitSales, franchiseTenure, welComeFolderStatus, welComeFolderDtm, trainingAmount, societyServiceamount, totalAmount, gstAmount, totalfranchisegstFund, legalCharges, legalChargesdue, totalgstCharges, totalPaidamount, dueFranchiseamt, kitCharges, numinitialKit, totalKitsamt, kitamtReceived, dueKitamount, installationDate, finaltotalamtDue, specialRemark, transporttravCharge, brsetupinstachargReceived, brsetupinstachargDue, travelAmount, receivedtravelAmount, duetravelAmount, transportCharges, transportAmtreceived, duetransportCharges, ledgerMarch, ledgerJune, ledgerSeptember, ledgerDecember, reminderAMCStatus1Dec, reminderAMCStatus10Dec, reminderAMCStatus15Dec, reminderAMCStatus19Dec, reminderAMCStatus20Dec, RemarkforAMCmail, InvoiceAMCClearance, PenaltyMailnoncle, invoiceNumberAll, upgradeUptoclass, branchStatus, brInstallationStatus, undertakingAck, optOnlineMarketing, insmatDispatchdate, DetailsReceiptmail, ConfBrinsScheduledemail, Materialrecdate, BrinsScheduleddate, BrinsScheduledemail, brInstalationRemark, videoFeedbackbr, writtenFeedbackbr, ShoppinPortSharedDate, ShoppinPortTraining, ShoppinPortTrainingDate, ShoppinPortRemark, returnItems, modeOfDespatch, NumOfBoxes, PoDNum, SpecificGiftOffer, ConfBrInsOverPhone, shortComming, solutionShortComming, customWebsiteLink,');
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
    function editBranches($branchesInfo, $branchesId)
    {
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_branches', $branchesInfo);
        return TRUE;
    }
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
        $this->db->where('userTbl.roleId', 15);
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

    function updateLegalDocuments($branchesId, $accessMatrix)
    {
        /*---Update Legal Documets--*/
        $this->db->where('branchesId', $branchesId);
        $this->db->update('tbl_legal_documents', $accessMatrix);

        return $this->db->affected_rows();
    }
    /*** This function is used to import csv file */
    public function insertCsvFile($data) {
        $this->db->insert('tbl_branches', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }
}