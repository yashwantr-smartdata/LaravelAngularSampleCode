import { Injectable } from '@angular/core';
import { environment } from 'src/environments/environment';
import { HttpClient } from '@angular/common/http';
import { GetterSetterService } from './getter-setter.service';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AppService {
  public url:string= environment.url;
  constructor(private http:HttpClient, private getSetService:GetterSetterService, private route:Router) { }

  socialSignInWithGoogle(userData){
    return this.http.post(this.url+'/api/user/socialLoginGoogle',userData);
  }

  socialSignInWithFb(userData){
    return this.http.post(this.url+'/api/user/socialLoginFb',userData);
  }

  userLogin(userData){
    return this.http.post(this.url+'/api/user/login',userData);
  }

  sendForgotPasswordLink(userData){
    return this.http.post(this.url+'/api/forgot_password',userData);
  }

  changePassword(userData) {
   
    return this.http.post(this.url + '/api/change_password', userData);
  }

  countries(){
    return this.http.get(this.url+'/api/country_list');
    // return this.http.get('http://172.24.1.174:8000/api/country_list');
  }

  sendData(userData){
    return this.http.post(this.url+'/api/user/register',userData);
  }

  submitPendingInitializedForm(data){
    return this.http.post(this.url+'/api/user/pending_initialized',data);
  }

  submitmobileVerificationForm(data){
    return this.http.post(this.url+'/api/send_message_on_registration',data);
  }

  submitVerificationForm(data){
    return this.http.post(this.url+'/api/customer/verify_code',data);
  }
  
  submitTaxationForm(data){
    return this.http.post(this.url+'/api/user/pending_taxation',data);
  }

  submitAddressForm(data){
    return this.http.post(this.url+'/api/user/pending_address',data);
  }
  
  submitNotificationForm(data){
    return this.http.post(this.url+'/api/user/pending_notifications',data);
  }

  getNotificationList(){
    return this.http.get(this.url+'/api/notifications_list');
  }
  /**
   * Service to maintain status
   */
  maintainStatus(){
    var routeArray = ['login','register'];
    routeArray.forEach(async val=>{
      if(window.location.href.includes(val)){
        await this.getSetService.setAuthPageStatus(false);
        await this.getSetService.setLoggedInStatus(false);
        await this.getSetService.setSignInStatus(false);
      }
      else{
        var userData = localStorage.getItem('stormboardz_frontend_userData');
        if(userData!=null){
          await this.getSetService.setLoggedInStatus(true);
          await this.getSetService.setSignInStatus(false);
        }else{
          await this.getSetService.setLoggedInStatus(false);
          await this.getSetService.setSignInStatus(true);
        }
      }
    })
  }

  /**
   * Function to logged out user session
   */
  clearUserSession(){
    localStorage.removeItem('stormboardz_frontend_userData');
    localStorage.removeItem('stormboardz_frontend_token');
  }

   /**
   * Function to reset password link
   */
  resetPassword(userData){
    userData.password_confirmation=userData.confirm_password;
    return this.http.post(this.url+'/api/recover_account',userData);
  }

  /**
   * Function to show Authorization message 
   */
  unAuthorizedUser(){
    
  }

  updateUserProfile(userData){
    return this.http.post(this.url+'/api/user/update_profile',userData);
  }

  uploadUserProfilePhoto(file){
    return this.http.post(this.url+'/api/user/add_update_profile_image',file);
  }

  getLanguageList(){
    return this.http.get(this.url+'/api/languages_list');
  }

  getCategoryList(){
    return this.http.get(this.url+'/api/item_category_list');
  }

  getData(userData){
    console.log('user userData ',userData);
   return this.http.post(this.url+'/api/get_chats',userData);
 }

 saveData(userData){
   
  return this.http.post(this.url+'/api/chats',userData);
}
}
