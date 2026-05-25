import React from "react";

const EditProfileGallery = ({

  galleryPreview,

  handleGallery,

}) => {

  return (

    <div className="mt-4">

      <label className="form-label">
        Gallery Images
      </label>

      <input
        type="file"
        multiple
        className="form-control"
        onChange={
          handleGallery
        }
      />

      <div className="d-flex gap-3 flex-wrap mt-3">

        {galleryPreview.map(
          (img, index) => (

            <img
              key={index}
              src={img}
              className="rounded"
              style={{
                width: "120px",
                height: "120px",
                objectFit: "cover",
              }}
            />

        ))}

      </div>

    </div>

  );

};

export default EditProfileGallery;
