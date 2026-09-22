from sqlalchemy import Column, BigInteger, String, Text, SmallInteger, ForeignKey, JSON
from sqlalchemy.orm import relationship
from database import Base


class Course(Base):
    __tablename__ = "courses_courses"

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    instructor_id = Column(BigInteger, nullable=False)
    title = Column(String(255), nullable=False)
    slug = Column(String(255), unique=True)
    subtitle = Column(String(255))
    description = Column(Text)
    category_id = Column(BigInteger)
    sub_category_id = Column(BigInteger)
    tags = Column(JSON)
    type = Column(SmallInteger, default=1)
    level = Column(SmallInteger, default=1)
    language_id = Column(BigInteger)
    learning_objectives = Column(JSON)
    prerequisites = Column(Text)
    status = Column(SmallInteger, default=1)
    content_length = Column(BigInteger, default=0)

    sections = relationship("Section", back_populates="course", lazy="selectin")
    faqs = relationship("Faq", back_populates="course", lazy="selectin")
    enrollments = relationship("Enrollment", back_populates="course", lazy="selectin")


class Section(Base):
    __tablename__ = "courses_sections"

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    course_id = Column(BigInteger, ForeignKey("courses_courses.id"), nullable=False)
    title = Column(String(255), nullable=False)
    description = Column(Text)

    course = relationship("Course", back_populates="sections")
    curriculums = relationship("Curriculum", back_populates="section", lazy="selectin")


class Curriculum(Base):
    __tablename__ = "courses_curriculums"

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    section_id = Column(BigInteger, ForeignKey("courses_sections.id"), nullable=False)
    title = Column(String(255), nullable=False)
    description = Column(Text)
    type = Column(String(20))
    media_path = Column(String(510))
    article_content = Column(Text)
    content_length = Column(BigInteger, default=0)
    sort_order = Column(BigInteger, default=0)
    is_preview = Column(SmallInteger, default=0)

    section = relationship("Section", back_populates="curriculums")


class Faq(Base):
    __tablename__ = "courses_faqs"

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    course_id = Column(BigInteger, ForeignKey("courses_courses.id"), nullable=False)
    question = Column(Text)
    answer = Column(Text)

    course = relationship("Course", back_populates="faqs")


class Enrollment(Base):
    __tablename__ = "courses_enrollments"

    id = Column(BigInteger, primary_key=True, autoincrement=True)
    student_id = Column(BigInteger, nullable=False)
    tutor_id = Column(BigInteger, nullable=False)
    course_id = Column(BigInteger, ForeignKey("courses_courses.id"), nullable=False)
    status = Column(SmallInteger, default=1)

    course = relationship("Course", back_populates="enrollments")
