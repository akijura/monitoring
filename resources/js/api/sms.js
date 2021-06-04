import request from '@/utils/request';
import Resource from '@/api/resourceSms';

class SmsResource extends Resource {
  constructor() {
    super('sms');
  }
}
export function getServerList() {
  return request({
    url: '/servers_list',
    method: 'get',
  });
}
export function getUserList() {
  return request({
    url: '/users_list',
    method: 'get',
  });
}
export function editList(user, phone) {
  return request({
    url: '/sms/' + user + '/' + phone,
    method: 'get',
  });
}
export function UpdateSmsConnection(data) {
  return request({
    url: '/sms/update',
    method: 'post',
    data,
  });
}
export function DeleteSms(phone_number, user_id) {
  return request({
    url: '/sms/deleteList/' + phone_number + '/user/' + user_id,
    method: 'delete',
  });
}
export { SmsResource as default };

