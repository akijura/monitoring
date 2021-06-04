import request from '@/utils/request';
import Resource from '@/api/resource';


class ServerResource extends Resource {
  constructor() {
    super('types');
  }
}
export function createServerType(data) {
  return request({
    url: '/server_type/create',
    method: 'post',
    data,
  });
}
export function fetchErrorsCount() {
  return request({
    url: '/server_type/list_count',
    method: 'get',
  });
}
export { ServerResource as default };

